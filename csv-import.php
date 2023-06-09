<?php
function glossario_csv_import_processing()
{
    global $wpdb;
    $terms_table            = $wpdb->prefix . 'terms';
    $term_taxonomy_table    = $wpdb->prefix . 'term_taxonomy';
    $posts_table            = $wpdb->prefix . 'posts';
    $glossario_submit_btn   = $_POST['glossario_submit_btn'] ?? '';

    if ($glossario_submit_btn == 'Upload glossario CSV') {

        $glossario_submit_btn = $_POST['glossario_submit_btn'] ?? '';
        if ('Upload glossario CSV' == $glossario_submit_btn) {

            $allowed_file_type = array('csv');
            $filename = $_FILES['glossario_csv_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array($ext, $allowed_file_type)) {
                $handle = fopen($_FILES['glossario_csv_file']['tmp_name'], "r");

                $csv_data_list = [];
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $csv_data_list[] = $data;
                }

                unset($csv_data_list[0]);

                foreach ($csv_data_list as $single_data) {
                    $title          = $single_data[0] ?? '';
                    $spelling       = $single_data[1] ?? '';
                    $description    = $single_data[2] ?? '';
                    $gallery        = $single_data[3] ?? '';
                    $category       = $single_data[4] ?? '';

                    $post_result = $wpdb->get_row("SELECT * FROM $posts_table WHERE post_title = '{$title}' AND post_type ='glossario' AND post_status ='publish'");

                    $is_title = $post_result->post_title ?? '';

                    if ($is_title) {
                    } else {

                        $cat_result = $wpdb->get_row("SELECT * FROM $terms_table, $term_taxonomy_table WHERE name = '{$category}' AND $terms_table.term_id=$term_taxonomy_table.term_id AND $term_taxonomy_table.taxonomy ='glossario-cat'");

                        $cat_id = 0;
                        if (isset($cat_result->term_id)) {
                            $cat_id = $cat_result->term_id;
                        } else {
                            $cat_defaults = array(
                                'taxonomy'             => 'glossario-cat',
                                'cat_name'             => $category,
                                'category_description' => '',
                                'category_nicename'    => '',
                                'category_parent'      => '',
                            );
                            $cat_id =   wp_insert_category($cat_defaults);
                        }

                        $my_post = array(
                            'post_title'    => $title,
                            'post_status'   => 'publish',
                            'post_author'   => 1,
                            'post_type'   => 'glossario',
                        );

                        $post_ID = wp_insert_post($my_post);

                        if ($cat_id != 0) {
                            $tags       = array($cat_id);
                            wp_set_post_terms($post_ID, $tags, 'glossario-cat');
                        }
                        
                        
                        // start multiple 
                        // $brand_cat = array();
                        // if ($cat_id > 0) {
                        //     $brand_cat[0] = $cat_id;
                        // }

                        // if ($reg_id > 0) {
                        //     $brand_cat[1] = $reg_id;
                        // }

                        // if ($city_id > 0) {
                        //     $brand_cat[2] = $city_id;
                        // }
                        // wp_set_post_terms($post_ID, $brand_cat, 'brand_cat');
                        // start multiple 

                        add_post_meta($post_ID, 'gmdl_title_tree', $spelling);
                        add_post_meta($post_ID, 'gmdl_description', $description);

                        $gallery_imgs = explode(',', $gallery);

                        if ($gallery_imgs) {
                            $total_gal_imgs = count($gallery_imgs);
                            $all_gal_img_ids = [];
                            for ($i = 0; $i < $total_gal_imgs; $i++) {
                                $image_id = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $gallery_imgs[$i]));

                                $all_gal_img_ids[] = $image_id[0];
                            }

                            add_post_meta($post_ID, 'gmdl_gallery', $all_gal_img_ids);
                        }
                    }
                    // end if 
                }
?>
                <div class="alert alert-success">
                    <strong>Successfully!</strong> Imported CSV file
                </div>
            <?php
            } else {
            ?>
                <div class="alert alert-danger">
                    <strong>Please</strong> Upload only CSV file
                </div>
<?php
            }
        }
    }
}
