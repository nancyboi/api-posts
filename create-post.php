<?php
add_action('init', 'create_wp_posts');

function create_wp_posts(){
    /**
     * get url from options
     * get posts from api
     */
    $options = get_option('my_options', []);
    $url = $options['my_option_1' ?? ''];
    if(!$url) return;

    /**
     * check if url is array and reset to empty if so
     */
    if (is_array($url)) {
        $url = '';
    }

    /**
     * return if url is empty or isn't a string
     */
    if (empty($url) || !is_string($url)) {
        error_log("API Error: URL is invalid.");
        
        return;
    }

    

    $response = wp_remote_get($url);

    /**
     * check if response is an error
     */
    if(is_wp_error($response)){
        error_log("API error: ".$response->get_error_message());
        debug_to_console("api error");
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    /**
     * if data exists
     */
    if($data){
        /**
         * loop through each post in data
         */
        foreach($data as $post){
            $post_title = $post['title'];
            $post_content = $post['body'];
            $post_status = 'publish';
            $post_author = $post['userId'];
            $post_type = 'post';

            /**
             * set slug as sanitized post title
             */
            $post_slug = sanitize_title($post_title);

            /**
             * check database for posts of the same type with the same slug
             */
            global $wpdb;
            $existing_post = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1",
                $post_slug, $post_type
            ));

            /**
             * if the post slug doesn't exist already
             */
            if (!$existing_post){
                $wordpress_post = [
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                    'post_status' => $post_status,
                    'post_author' => $post_author,
                    'post_type' => $post_type,
                    'post_name' => $post_slug
                ];

                wp_insert_post($wordpress_post);
            }
        }
    }
}