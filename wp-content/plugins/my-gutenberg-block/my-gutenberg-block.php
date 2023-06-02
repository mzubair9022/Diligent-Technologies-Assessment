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




function add_news_meta_box() {
  add_meta_box(
      'news_meta_box',
      'News Meta',
      'render_news_meta_box',
      'news',
      'normal',
      'default'
  );
}
add_action('add_meta_boxes', 'add_news_meta_box');

function render_news_meta_box($post) {
  // Retrieve the existing meta values
  $author = get_post_meta($post->ID, 'news_author', true);
  $date = get_post_meta($post->ID, 'news_date', true);

  // Add a nonce field to verify the data later
  wp_nonce_field('news_meta_box', 'news_meta_box_nonce');

  // Display the fields
  echo '<label for="news_author">Author:</label>';
  echo '<input type="text" id="news_author" name="news_author" value="' . esc_attr($author) . '">';
  echo '<br>';
  echo '<label for="news_date">Date:</label>';
  echo '<input type="text" id="news_date" name="news_date" value="' . esc_attr($date) . '">';
}

function save_news_meta_box_data($post_id) {
  // Verify the nonce
  if (!isset($_POST['news_meta_box_nonce']) || !wp_verify_nonce($_POST['news_meta_box_nonce'], 'news_meta_box')) {
      return;
  }

  // Check if the current user has permission to save the data
  if (!current_user_can('edit_post', $post_id)) {
      return;
  }

  // Sanitize and save the meta box data
  if (isset($_POST['news_author'])) {
      update_post_meta($post_id, 'news_author', sanitize_text_field($_POST['news_author']));
  }
  if (isset($_POST['news_date'])) {
      update_post_meta($post_id, 'news_date', sanitize_text_field($_POST['news_date']));
  }
}
add_action('save_post', 'save_news_meta_box_data');
