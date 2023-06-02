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
    
    if (isset($data['articles'][0]['source']['name'])) {
        $source_name = $data['articles'][0]['source']['name'];
    }
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
            if (isset($data['articles'][0]['source']['name'])) {
                $source_name = $data['articles'][0]['source']['name'];
                $post_data['tags_input'] = array($source_name);
            }
            $post_id = wp_insert_post($post_data);
        }
        ?>
      <h3><?php echo $article['title']; ?></h3>
      <p><?php echo $article['description']; ?></p>
      <p> Source  : <?php echo $source_name;?> </p>
    <?php } ?>
  </div>
  <?php
  return ob_get_clean();
}

function register_news_cpt() {
    $labels = array(
        'name' => 'News',
        'singular_name' => 'News',
        'menu_name' => 'News',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New News',
        'edit_item' => 'Edit News',
        'new_item' => 'New News',
        'view_item' => 'View News',
        'search_items' => 'Search News',
        'not_found' => 'No News found',
        'not_found_in_trash' => 'No News found in Trash',
        'parent_item_colon' => 'Parent News:',
        'all_items' => 'All News',
        'archives' => 'News Archives',
        'insert_into_item' => 'Insert into News',
        'uploaded_to_this_item' => 'Uploaded to this News',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'filter_items_list' => 'Filter News list',
        'items_list_navigation' => 'News list navigation',
        'items_list' => 'News list'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'supports' => array('title', 'editor'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'news'),
        'menu_position' => 20,
        'menu_icon' => 'dashicons-feedback',
    );

    register_post_type('news', $args);
}
add_action('init', 'register_news_cpt');
