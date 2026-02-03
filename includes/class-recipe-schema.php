<?php
/**
 * Recipe Schema Class
 *
 * Generates Recipe JSON-LD schema markup from parsed recipe data
 *
 * @package CJC_Auto_Schema
 */

defined( 'ABSPATH' ) || exit;

class CJC_Recipe_Schema {

    /**
     * The post object
     *
     * @var WP_Post
     */
    private $post;

    /**
     * The parsed recipe data
     *
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param WP_Post $post The post to generate schema for
     */
    public function __construct( $post ) {
        $this->post = $post;

        // Parse the recipe data
        $parser     = new CJC_Recipe_Parser( $post );
        $this->data = $parser->parse();
    }

    /**
     * Check if this post should have recipe schema
     *
     * @return bool
     */
    public function should_output_schema() {
        // Must be a single post
        if ( $this->post->post_type !== 'post' ) {
            return false;
        }

        // Must be in recipe category or child categories
        $categories     = wp_get_post_categories( $this->post->ID );
        $recipe_cats    = array_merge( array( CJC_RECIPE_CATEGORY_ID ), CJC_RECIPE_CHILD_CATEGORIES );
        $is_recipe_post = ! empty( array_intersect( $categories, $recipe_cats ) );

        if ( ! $is_recipe_post ) {
            return false;
        }

        // Check if Rank Math is already handling recipe schema
        $rank_math_schema = get_post_meta( $this->post->ID, 'rank_math_rich_snippet', true );
        if ( $rank_math_schema === 'recipe' ) {
            return false;
        }

        // Must have minimum required data (ingredients and instructions)
        if ( empty( $this->data['ingredients'] ) || empty( $this->data['instructions'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Generate the schema array
     *
     * @return array Schema data
     */
    public function generate_schema() {
        $schema = array(
            '@context' => 'https://schema.org/',
            '@type'    => 'Recipe',
            'name'     => $this->data['name'],
            'author'   => array(
                '@type' => 'Person',
                'name'  => 'Curtis J. Cooks',
            ),
        );

        // Add description if available
        if ( ! empty( $this->data['description'] ) ) {
            $schema['description'] = $this->data['description'];
        }

        // Add image if available
        if ( ! empty( $this->data['image'] ) ) {
            $schema['image'] = $this->data['image'];
        }

        // Add date published
        if ( ! empty( $this->data['datePublished'] ) ) {
            $schema['datePublished'] = $this->data['datePublished'];
        }

        // Add times if available
        if ( ! empty( $this->data['prepTime'] ) ) {
            $schema['prepTime'] = $this->data['prepTime'];
        }

        if ( ! empty( $this->data['cookTime'] ) ) {
            $schema['cookTime'] = $this->data['cookTime'];
        }

        if ( ! empty( $this->data['totalTime'] ) ) {
            $schema['totalTime'] = $this->data['totalTime'];
        }

        // Add yield if available
        if ( ! empty( $this->data['yield'] ) ) {
            $schema['recipeYield'] = $this->data['yield'];
        }

        // Add category
        if ( ! empty( $this->data['category'] ) ) {
            $schema['recipeCategory'] = $this->data['category'];
        }

        // Add cuisine
        if ( ! empty( $this->data['cuisine'] ) ) {
            $schema['recipeCuisine'] = $this->data['cuisine'];
        }

        // Add ingredients
        $schema['recipeIngredient'] = $this->data['ingredients'];

        // Add instructions as HowToStep objects
        $schema['recipeInstructions'] = array();
        foreach ( $this->data['instructions'] as $step ) {
            $schema['recipeInstructions'][] = array(
                '@type' => 'HowToStep',
                'text'  => $step,
            );
        }

        return $schema;
    }

    /**
     * Get the JSON-LD output
     *
     * @return string JSON-LD script tag
     */
    public function get_json_ld() {
        if ( ! $this->should_output_schema() ) {
            return '';
        }

        $schema = $this->generate_schema();

        return '<script type="application/ld+json">' . "\n" .
               wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" .
               '</script>' . "\n";
    }
}
