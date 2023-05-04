<?php 

csv_generator();
function csv_generator()
{
    header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=glossario.csv');
    echo "\xEF\xBB\xBF";

    $glossario_args = array(
        'post_type' => 'glossario',
        'posts_per_page' => -1,
    );
    $glossario_query = new WP_Query($glossario_args);

    $output = fopen("php://output", "w");
    fputcsv($output, array('Title', 'Spelling', 'Description', 'Gallery', 'Category'));

    if ($glossario_query->have_posts()) {
        while ($glossario_query->have_posts()) {
            $glossario_query->the_post();
            $post_id                = get_the_ID();

            $data                   =  [];
            $data['title']          = get_the_title();
            $data['spelling']       = get_field('gmdl_title_tree');
            $data['description']    = strip_tags(get_field('gmdl_description'));

            $gmdl_gallery        = get_field('gmdl_gallery');

            if ($gmdl_gallery) {
                $make_gal_img_url = [];
                foreach ($gmdl_gallery as $single_gal_id) {
                    $make_gal_img_url[] = wp_get_attachment_image_url($single_gal_id);
                }
                $data['gallery']        = implode(',', $make_gal_img_url);
            }

            $glossario_cat          = get_the_terms($post_id, 'glossario-cat');

            $gcat_list = [];
            if ($glossario_cat) {
                foreach ($glossario_cat as $single_gcat) {
                    $gcat_list[] = $single_gcat->name;
                }
            }


            $data['category'] = implode(',', $gcat_list);

            fputcsv($output, $data);
            // echo "<pre>";
            // print_r($data);
        }
        wp_reset_query();
    }

    fclose($output);
    exit();
}
