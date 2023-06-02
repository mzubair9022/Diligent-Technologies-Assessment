<?php
/*
Plugin Name: My Gutenberg Block
Description: Custom Gutenberg block that fetches data from the GNews API.
Version: 1.0
Author: Your Name
*/

// Register the Gutenberg block
function my_gutenberg_block_register() {
  wp_register_script(
    'my-gutenberg-block-script',
    plugins_url('my-gutenberg-block.js', __FILE__),
    array('wp-blocks', 'wp-components', 'wp-element')
  );

  register_block_type('my-gutenberg-block/my-gutenberg-block', array(
    'editor_script' => 'my-gutenberg-block-script',
    'render_callback' => 'my_gutenberg_block_render'
  ));
  
}
add_action('init', 'my_gutenberg_block_register');

// Server-side rendering function
function my_gutenberg_block_render($attributes) {
  $api_key = 'e80260a8e09f413a1beee979bfcff576';
  $url = 'https://gnews.io/api/v4/top-headlines?token=' . $api_key;

  $response = wp_remote_get($url);
    
  if (is_wp_error($response)) {
    return 'Error: ' . $response->get_error_message();
  }

  $body = wp_remote_retrieve_body($response);
  $data = json_decode($body, true);
  
  ob_start();
  ?>
  <div>
    <?php foreach ($data['articles'] as $article) {
        
        // Check if a post with a similar title already exists
        $existing_post = get_page_by_title($article['title'], OBJECT, 'news');
        if (!$existing_post) {
            // Save the API response as a Custom Post
            $post_data = array(
                'post_title' => $article['title'],
                'post_content' => $article['description'],
                'post_status' => 'publish',
                'post_type' => 'news',
            );
            
        }
        ?>
        <h3><?php echo $article['title']; ?></h3>
        <p><?php echo $article['description']; ?></p>
    <?php } ?>
  </div>
  <?php
  return ob_get_clean();
}

