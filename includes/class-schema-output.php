<?php
/**
 * Schema Output Class
 *
 * Handles outputting schema JSON-LD in wp_head
 *
 * @package CJC_Auto_Schema
 */

defined( 'ABSPATH' ) || exit;

class CJC_Schema_Output {

    /**
     * Constructor - register hooks
     */
    public function __construct() {
        // Hook into wp_head at priority 25 (after theme's recipe schema at 20)
        add_action( 'wp_head', array( $this, 'output_schema' ), 25 );
    }

    /**
     * Output the appropriate schema for the current page
     */
    public function output_schema() {
        // Only run on singular pages
        if ( ! is_singular() ) {
            return;
        }

        global $post;

        if ( ! $post ) {
            return;
        }

        // Get the post slug
        $slug = $post->post_name;

        // Check for FAQ schema on pillar pages (works for both pages and posts)
        if ( in_array( $slug, CJC_PILLAR_PAGE_SLUGS, true ) ) {
            $this->output_faq_schema( $slug, $post->ID );
            return;
        }

        // Check for Recipe schema on posts in recipe categories
        if ( is_single() && $post->post_type === 'post' ) {
            $this->output_recipe_schema( $post );
        }
    }

    /**
     * Output Recipe schema for a post
     *
     * @param WP_Post $post The post object
     */
    private function output_recipe_schema( $post ) {
        // Check if post is in a recipe category
        $categories  = wp_get_post_categories( $post->ID );
        $recipe_cats = array_merge( array( CJC_RECIPE_CATEGORY_ID ), CJC_RECIPE_CHILD_CATEGORIES );

        if ( empty( array_intersect( $categories, $recipe_cats ) ) ) {
            return;
        }

        $recipe_schema = new CJC_Recipe_Schema( $post );
        $json_ld       = $recipe_schema->get_json_ld();

        if ( ! empty( $json_ld ) ) {
            echo "\n<!-- CJC Auto Schema: Recipe -->\n";
            echo $json_ld;
            echo "<!-- /CJC Auto Schema -->\n";
        }
    }

    /**
     * Output FAQ schema for a page
     *
     * @param string $slug    The page slug
     * @param int    $page_id The page ID
     */
    private function output_faq_schema( $slug, $page_id ) {
        $faq_schema = new CJC_FAQ_Schema( $slug, $page_id );
        $json_ld    = $faq_schema->get_json_ld();

        if ( ! empty( $json_ld ) ) {
            echo "\n<!-- CJC Auto Schema: FAQ -->\n";
            echo $json_ld;
            echo "<!-- /CJC Auto Schema -->\n";
        }
    }

    /**
     * Check if the current content already has schema from another source
     *
     * @param int    $post_id     The post/page ID
     * @param string $schema_type The type of schema ('recipe' or 'faq')
     * @return bool True if schema already exists
     */
    public static function has_existing_schema( $post_id, $schema_type ) {
        // Check Rank Math rich snippet setting
        $rank_math_schema = get_post_meta( $post_id, 'rank_math_rich_snippet', true );

        if ( $rank_math_schema === $schema_type ) {
            return true;
        }

        return false;
    }
}
