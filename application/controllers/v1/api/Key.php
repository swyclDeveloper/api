<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Key extends API_Controller {

    protected $methods = [
            'index_put' => ['level' => 10, 'limit' => 10],
            'index_delete' => ['level' => 10],
            'level_post' => ['level' => 10],
            'regenerate_post' => ['level' => 10],
        ];
	

	public function keyCheck_get($key){
		if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400); // BAD_REQUEST (400) being the HTTP response code
        } else {
			$this->response([
                'status' => true,
                'message' => 'key is alive'
            ], 200); // OK (200) being the HTTP response code
		}
	}
	
	public function test_get(){
		
	}

    /**
     * Remove a key from the database to stop it working
     *
     * @access public
     * @return void
     */
    public function index_delete()
    {
		//exdebug('index_delete');exit;
        $key = $this->delete('key');

        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400); // BAD_REQUEST (400) being the HTTP response code
        }

        // Destroy it
        $this->_delete_key($key);

        // Respond that the key was destroyed
        $this->response([
            'status' => true,
            'message' => 'API key was deleted'
            ], 204); // NO_CONTENT (204) being the HTTP response code
    }

    /**
     * Change the level
     *
     * @access public
     * @return void
     */
    public function level_post()
    {
		$this->response([
                'status' => false,
                'message' => 'Could not update the key level'
            ], 500); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
		//exdebug('level_post');exit;
        $key = $this->post('key');
        $new_level = $this->post('level');

        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400); // BAD_REQUEST (400) being the HTTP response code
        }

        // Update the key level
        if ($this->_update_key($key, ['level' => $new_level]))
        {
            $this->response([
                'status' => true,
                'message' => 'API key was updated'
            ], 200); // OK (200) being the HTTP response code
        }
        else
        {
            $this->response([
                'status' => false,
                'message' => 'Could not update the key level'
            ], 500); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
    }

    /**
     * Suspend a key
     *
     * @access public
     * @return void
     */
    public function suspend_post()
    {
		//exdebug('suspend_post');exit;
        $key = $this->post('key');

        // Does this key exist?
        if (!$this->_key_exists($key))
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400); // BAD_REQUEST (400) being the HTTP response code
        }

        // Update the key level
        if ($this->_update_key($key, ['level' => 0]))
        {
            $this->response([
                'status' => true,
                'message' => 'Key was suspended'
            ], 200); // OK (200) being the HTTP response code
        }
        else
        {
            $this->response([
                'status' => false,
                'message' => 'Could not suspend the user'
            ], 500); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
    }

    /**
     * Regenerate a key
     *
     * @access public
     * @return void
     */
    public function regenerate_post()
    {
		//exdebug('regenerate_post');exit;
        $old_key = $this->post('key');
        $key_details = $this->_get_key($old_key);

        // Does this key exist?
        if (!$key_details)
        {
            // It doesn't appear the key exists
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400); // BAD_REQUEST (400) being the HTTP response code
        }

        // Build a new key
        $new_key = $this->_generate_key();

        // Insert the new key
        if ($this->_insert_key($new_key, ['level' => $key_details->level, 'ignore_limits' => $key_details->ignore_limits]))
        {
            // Suspend old key
            $this->_update_key($old_key, ['level' => 0]);

            $this->response([
                'status' => true,
                'key' => $new_key
            ], 201); // CREATED (201) being the HTTP response code
        }
        else
        {
            $this->response([
                'status' => false,
                'message' => 'Could not save the key'
            ], 500); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
        }
    }

    /* Helper Methods */

    private function _generate_key()
    {
        do
        {
            // Generate a random salt
            $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

            // If an error occurred, then fall back to the previous method
            if ($salt === false)
            {
                $salt = hash('sha256', time() . mt_rand());
            }

            $new_key = substr($salt, 0, config_item('rest_key_length'));
        }
        while ($this->_key_exists($new_key));

        return $new_key;
    }

    /* Private Data Methods */

    private function _get_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
			->where('status', 'Y')
            ->get(config_item('rest_keys_table'))
            ->row();
    }

    private function _key_exists($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
			->where('status', 'Y')
            ->count_all_results(config_item('rest_keys_table')) > 0;
    }

    private function _insert_key($key, $data)
    {
        $data[config_item('rest_key_column')] = $key;
        $data['date_created'] = function_exists('now') ? now() : time();

        return $this->rest->db
            ->set($data)
            ->insert(config_item('rest_keys_table'));
    }

    private function _update_key($key, $data)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->update(config_item('rest_keys_table'), $data);
    }

    private function _delete_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->delete(config_item('rest_keys_table'));
    }

}
