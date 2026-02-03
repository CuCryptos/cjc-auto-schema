<?php
/**
 * Recipe Parser Class
 *
 * Parses post content to extract recipe data (ingredients, instructions, times, etc.)
 *
 * @package CJC_Auto_Schema
 */

defined( 'ABSPATH' ) || exit;

class CJC_Recipe_Parser {

    /**
     * The post content to parse
     *
     * @var string
     */
    private $content;

    /**
     * The post object
     *
     * @var WP_Post
     */
    private $post;

    /**
     * Constructor
     *
     * @param WP_Post $post The post to parse
     */
    public function __construct( $post ) {
        $this->post    = $post;
        $this->content = $post->post_content;
    }

    /**
     * Parse all recipe data from the post
     *
     * @return array Parsed recipe data
     */
    public function parse() {
        return array(
            'name'         => $this->get_name(),
            'description'  => $this->get_description(),
            'image'        => $this->get_image(),
            'datePublished'=> $this->get_date_published(),
            'prepTime'     => $this->get_prep_time(),
            'cookTime'     => $this->get_cook_time(),
            'totalTime'    => $this->get_total_time(),
            'yield'        => $this->get_yield(),
            'category'     => $this->get_category(),
            'cuisine'      => $this->get_cuisine(),
            'ingredients'  => $this->get_ingredients(),
            'instructions' => $this->get_instructions(),
        );
    }

    /**
     * Get the recipe name (post title)
     *
     * @return string
     */
    public function get_name() {
        return get_the_title( $this->post );
    }

    /**
     * Get the recipe description
     * Priority: Post excerpt > Rank Math meta > First paragraph
     *
     * @return string
     */
    public function get_description() {
        // Try post excerpt first
        if ( ! empty( $this->post->post_excerpt ) ) {
            return wp_strip_all_tags( $this->post->post_excerpt );
        }

        // Try Rank Math meta description
        $rank_math_desc = get_post_meta( $this->post->ID, 'rank_math_description', true );
        if ( ! empty( $rank_math_desc ) ) {
            return wp_strip_all_tags( $rank_math_desc );
        }

        // Fall back to first paragraph
        $content = apply_filters( 'the_content', $this->content );
        preg_match( '/<p[^>]*>(.*?)<\/p>/is', $content, $matches );
        if ( ! empty( $matches[1] ) ) {
            $first_para = wp_strip_all_tags( $matches[1] );
            // Limit to 160 characters
            if ( strlen( $first_para ) > 160 ) {
                $first_para = substr( $first_para, 0, 157 ) . '...';
            }
            return $first_para;
        }

        return '';
    }

    /**
     * Get the featured image URL
     *
     * @return string
     */
    public function get_image() {
        $image_id = get_post_thumbnail_id( $this->post->ID );
        if ( $image_id ) {
            $image = wp_get_attachment_image_src( $image_id, 'full' );
            if ( $image ) {
                return $image[0];
            }
        }
        return '';
    }

    /**
     * Get the publish date in ISO 8601 format
     *
     * @return string
     */
    public function get_date_published() {
        return get_the_date( 'c', $this->post );
    }

    /**
     * Get prep time from content
     *
     * @return string ISO 8601 duration or empty string
     */
    public function get_prep_time() {
        return $this->extract_time( array( 'Prep', 'Preparation', 'Active' ) );
    }

    /**
     * Get cook time from content
     *
     * @return string ISO 8601 duration or empty string
     */
    public function get_cook_time() {
        return $this->extract_time( array( 'Cook', 'Cooking', 'Bake', 'Baking' ) );
    }

    /**
     * Get total time from content
     *
     * @return string ISO 8601 duration or empty string
     */
    public function get_total_time() {
        $total = $this->extract_time( array( 'Total', 'Ready in' ) );

        // If no total time found, try to calculate from prep + cook
        if ( empty( $total ) ) {
            $prep = $this->get_prep_time();
            $cook = $this->get_cook_time();

            if ( ! empty( $prep ) && ! empty( $cook ) ) {
                $prep_minutes = $this->iso_duration_to_minutes( $prep );
                $cook_minutes = $this->iso_duration_to_minutes( $cook );
                $total = $this->minutes_to_iso_duration( $prep_minutes + $cook_minutes );
            }
        }

        return $total;
    }

    /**
     * Extract time from content using keywords
     *
     * @param array $keywords Time type keywords to search for
     * @return string ISO 8601 duration or empty string
     */
    private function extract_time( $keywords ) {
        $keyword_pattern = implode( '|', $keywords );

        // Pattern: "Prep Time: 30 minutes" or "Prep: 30 min" etc.
        $pattern = '/(' . $keyword_pattern . ')\s*(?:Time)?[:\s]+(\d+)\s*(minute|minutes|min|mins|hour|hours|hr|hrs)/i';

        if ( preg_match( $pattern, $this->content, $matches ) ) {
            $value = intval( $matches[2] );
            $unit  = strtolower( $matches[3] );

            // Convert to minutes
            if ( strpos( $unit, 'hour' ) !== false || strpos( $unit, 'hr' ) !== false ) {
                $value = $value * 60;
            }

            return $this->minutes_to_iso_duration( $value );
        }

        // Also check for "1 hour 30 minutes" format
        $pattern2 = '/(' . $keyword_pattern . ')\s*(?:Time)?[:\s]+(\d+)\s*(?:hour|hours|hr|hrs)?\s*(?:and)?\s*(\d+)?\s*(minute|minutes|min|mins)?/i';

        if ( preg_match( $pattern2, $this->content, $matches ) ) {
            $hours   = intval( $matches[2] );
            $minutes = isset( $matches[3] ) ? intval( $matches[3] ) : 0;
            $total   = ( $hours * 60 ) + $minutes;

            if ( $total > 0 ) {
                return $this->minutes_to_iso_duration( $total );
            }
        }

        return '';
    }

    /**
     * Convert minutes to ISO 8601 duration format
     *
     * @param int $minutes Number of minutes
     * @return string ISO 8601 duration (e.g., "PT30M" or "PT1H30M")
     */
    private function minutes_to_iso_duration( $minutes ) {
        if ( $minutes <= 0 ) {
            return '';
        }

        $hours = floor( $minutes / 60 );
        $mins  = $minutes % 60;

        $duration = 'PT';
        if ( $hours > 0 ) {
            $duration .= $hours . 'H';
        }
        if ( $mins > 0 ) {
            $duration .= $mins . 'M';
        }

        return $duration;
    }

    /**
     * Convert ISO 8601 duration to minutes
     *
     * @param string $iso ISO 8601 duration
     * @return int Minutes
     */
    private function iso_duration_to_minutes( $iso ) {
        $minutes = 0;

        if ( preg_match( '/PT(?:(\d+)H)?(?:(\d+)M)?/', $iso, $matches ) ) {
            if ( ! empty( $matches[1] ) ) {
                $minutes += intval( $matches[1] ) * 60;
            }
            if ( ! empty( $matches[2] ) ) {
                $minutes += intval( $matches[2] );
            }
        }

        return $minutes;
    }

    /**
     * Get yield/servings from content
     *
     * @return string Yield string or empty
     */
    public function get_yield() {
        // Pattern: "Serves: 4" or "Yield: 6 servings" or "Makes: 12 cookies"
        $pattern = '/(Serve|Serves|Serving|Servings|Yield|Yields|Make|Makes)[s]?[:\s]+(\d+)(?:\s+(\w+))?/i';

        if ( preg_match( $pattern, $this->content, $matches ) ) {
            $value = intval( $matches[2] );
            $unit  = isset( $matches[3] ) ? strtolower( $matches[3] ) : 'servings';

            // Standardize to "X servings" unless it's a specific item
            if ( in_array( $unit, array( 'serving', 'servings', 'people', 'persons' ), true ) ) {
                return $value . ' servings';
            }

            return $value . ' ' . $unit;
        }

        return '';
    }

    /**
     * Get recipe category based on WordPress category
     *
     * @return string Recipe category
     */
    public function get_category() {
        $categories = wp_get_post_categories( $this->post->ID );

        // Category mappings
        $category_map = array(
            859 => 'Appetizer',      // Poke & Seafood
            860 => 'Main Course',    // Island Comfort
            861 => 'Dessert',        // Tropical Treats
            862 => 'Beverage',       // Island Drinks
            873 => 'Breakfast',      // Hawaiian Breakfast
            874 => 'Appetizer',      // Pupus & Snacks
            866 => '',               // Quick & Easy - infer from content
        );

        foreach ( $categories as $cat_id ) {
            if ( isset( $category_map[ $cat_id ] ) && ! empty( $category_map[ $cat_id ] ) ) {
                return $category_map[ $cat_id ];
            }
        }

        // Try to infer from content for Quick & Easy or general Recipes category
        if ( preg_match( '/(breakfast|brunch|morning)/i', $this->content ) ) {
            return 'Breakfast';
        }
        if ( preg_match( '/(dessert|sweet|cake|cookie|pie)/i', $this->content ) ) {
            return 'Dessert';
        }
        if ( preg_match( '/(drink|beverage|cocktail|smoothie)/i', $this->content ) ) {
            return 'Beverage';
        }
        if ( preg_match( '/(appetizer|pupu|snack|starter)/i', $this->content ) ) {
            return 'Appetizer';
        }

        // Default to Main Course
        return 'Main Course';
    }

    /**
     * Get recipe cuisine
     *
     * @return string Cuisine type
     */
    public function get_cuisine() {
        // Default to Hawaiian for this site
        return 'Hawaiian';
    }

    /**
     * Get ingredients from content
     *
     * @return array List of ingredient strings
     */
    public function get_ingredients() {
        $ingredients = array();

        // Look for heading containing "Ingredient" followed by a list
        $pattern = '/<h[2-6][^>]*>.*?Ingredient.*?<\/h[2-6]>\s*<[uo]l[^>]*>(.*?)<\/[uo]l>/is';

        if ( preg_match( $pattern, $this->content, $matches ) ) {
            $ingredients = $this->extract_list_items( $matches[1] );
        }

        // If not found, try looking for "Ingredients" as a strong/bold text
        if ( empty( $ingredients ) ) {
            $pattern2 = '/<(?:strong|b)[^>]*>.*?Ingredient.*?<\/(?:strong|b)>\s*<[uo]l[^>]*>(.*?)<\/[uo]l>/is';
            if ( preg_match( $pattern2, $this->content, $matches ) ) {
                $ingredients = $this->extract_list_items( $matches[1] );
            }
        }

        // Try WordPress block patterns (Gutenberg)
        if ( empty( $ingredients ) ) {
            // Look for wp:list blocks after ingredient headings
            $pattern3 = '/ingredient[s]?.*?(?:<!-- wp:list -->|<ul[^>]*>)(.*?)(?:<!-- \/wp:list -->|<\/ul>)/is';
            if ( preg_match( $pattern3, $this->content, $matches ) ) {
                $ingredients = $this->extract_list_items( $matches[1] );
            }
        }

        return $ingredients;
    }

    /**
     * Get instructions from content
     *
     * @return array List of instruction strings
     */
    public function get_instructions() {
        $instructions = array();

        // Keywords that typically precede instructions
        $keywords = 'Instruction|Instructions|Direction|Directions|Method|Steps|How to Make|Preparation';

        // Look for heading containing instruction keywords followed by a list
        $pattern = '/<h[2-6][^>]*>.*?(?:' . $keywords . ').*?<\/h[2-6]>\s*<[uo]l[^>]*>(.*?)<\/[uo]l>/is';

        if ( preg_match( $pattern, $this->content, $matches ) ) {
            $instructions = $this->extract_list_items( $matches[1] );
        }

        // If not found, try looking for keywords as strong/bold text
        if ( empty( $instructions ) ) {
            $pattern2 = '/<(?:strong|b)[^>]*>.*?(?:' . $keywords . ').*?<\/(?:strong|b)>\s*<[uo]l[^>]*>(.*?)<\/[uo]l>/is';
            if ( preg_match( $pattern2, $this->content, $matches ) ) {
                $instructions = $this->extract_list_items( $matches[1] );
            }
        }

        // Try WordPress block patterns (Gutenberg)
        if ( empty( $instructions ) ) {
            $pattern3 = '/(?:' . $keywords . ').*?(?:<!-- wp:list -->|<ol[^>]*>)(.*?)(?:<!-- \/wp:list -->|<\/ol>)/is';
            if ( preg_match( $pattern3, $this->content, $matches ) ) {
                $instructions = $this->extract_list_items( $matches[1] );
            }
        }

        return $instructions;
    }

    /**
     * Extract list items from HTML list content
     *
     * @param string $html HTML containing <li> elements
     * @return array List of text items
     */
    private function extract_list_items( $html ) {
        $items = array();

        // Match all <li> elements
        preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $html, $matches );

        if ( ! empty( $matches[1] ) ) {
            foreach ( $matches[1] as $item ) {
                // Clean up the item text
                $text = wp_strip_all_tags( $item );
                $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
                $text = trim( $text );

                // Remove leading numbers/bullets that might be in text
                $text = preg_replace( '/^\d+[\.\)]\s*/', '', $text );

                if ( ! empty( $text ) ) {
                    $items[] = $text;
                }
            }
        }

        return $items;
    }

    /**
     * Check if the parsed data has minimum required fields for valid schema
     *
     * @return bool True if valid, false otherwise
     */
    public function is_valid_recipe() {
        $data = $this->parse();

        // Must have at least one ingredient and one instruction
        return ! empty( $data['ingredients'] ) && ! empty( $data['instructions'] );
    }
}
