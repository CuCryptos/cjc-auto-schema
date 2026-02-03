<?php
/**
 * Admin Settings Class
 *
 * Provides admin UI for testing and previewing schema output
 *
 * @package CJC_Auto_Schema
 */

defined( 'ABSPATH' ) || exit;

class CJC_Admin_Settings {

    /**
     * Constructor - register hooks
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_management_page(
            'CJC Auto Schema',
            'CJC Auto Schema',
            'manage_options',
            'cjc-auto-schema',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Enqueue admin styles
     *
     * @param string $hook The current admin page hook
     */
    public function enqueue_admin_styles( $hook ) {
        if ( $hook !== 'tools_page_cjc-auto-schema' ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', '
            .cjc-schema-preview {
                background: #f5f5f5;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 10px 0;
                overflow-x: auto;
                font-family: monospace;
                font-size: 12px;
                white-space: pre-wrap;
                word-wrap: break-word;
            }
            .cjc-schema-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 20px;
                margin-bottom: 20px;
            }
            .cjc-schema-card h3 {
                margin-top: 0;
            }
            .cjc-success { color: #46b450; }
            .cjc-warning { color: #ffb900; }
            .cjc-error { color: #dc3232; }
        ' );
    }

    /**
     * Render the admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>CJC Auto Schema</h1>
            <p>This plugin automatically generates Recipe and FAQ schema markup for CurtisJCooks.com.</p>

            <div class="cjc-schema-card">
                <h2>Recipe Schema</h2>
                <p>Recipe schema is automatically generated for posts in the Recipes category (ID: <?php echo CJC_RECIPE_CATEGORY_ID; ?>) and its child categories.</p>

                <h3>Child Categories:</h3>
                <ul>
                    <?php
                    foreach ( CJC_RECIPE_CHILD_CATEGORIES as $cat_id ) {
                        $cat = get_category( $cat_id );
                        if ( $cat ) {
                            echo '<li>' . esc_html( $cat->name ) . ' (ID: ' . $cat_id . ')</li>';
                        }
                    }
                    ?>
                </ul>

                <h3>Test a Recipe Post</h3>
                <?php $this->render_recipe_test_form(); ?>
            </div>

            <div class="cjc-schema-card">
                <h2>FAQ Schema</h2>
                <p>FAQ schema is generated for pillar pages based on their URL slug:</p>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Slug</th>
                            <th>Page Found</th>
                            <th>FAQs</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( CJC_PILLAR_PAGE_SLUGS as $slug ) {
                            // Try to find page by slug
                            $page = get_page_by_path( $slug );
                            if ( ! $page ) {
                                // Also check posts (pillar pages might be posts)
                                $posts = get_posts( array(
                                    'name'        => $slug,
                                    'post_type'   => array( 'post', 'page' ),
                                    'post_status' => 'publish',
                                    'numberposts' => 1,
                                ) );
                                $page = ! empty( $posts ) ? $posts[0] : null;
                            }
                            $faq_data = CJC_FAQ_Schema::get_faq_data_by_slug( $slug );
                            ?>
                            <tr>
                                <td><code><?php echo esc_html( $slug ); ?></code></td>
                                <td>
                                    <?php if ( $page ) : ?>
                                        <a href="<?php echo get_permalink( $page->ID ); ?>" target="_blank">
                                            <?php echo esc_html( $page->post_title ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="cjc-warning">Not found (will work when page exists)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $faq_data ? count( $faq_data ) . ' Q&As' : '0'; ?></td>
                                <td>
                                    <?php if ( $faq_data ) : ?>
                                        <span class="cjc-success">✓ Ready</span>
                                    <?php else : ?>
                                        <span class="cjc-error">✗ No FAQ data</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <h3>Preview FAQ Schema</h3>
                <?php $this->render_faq_preview_form(); ?>
            </div>

            <div class="cjc-schema-card">
                <h2>Verification</h2>
                <p>After the plugin is active, verify schema output:</p>
                <ol>
                    <li>Visit a recipe post and view page source</li>
                    <li>Search for <code>CJC Auto Schema</code> or <code>application/ld+json</code></li>
                    <li>Test with <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
                </ol>
            </div>
        </div>
        <?php
    }

    /**
     * Render recipe test form
     */
    private function render_recipe_test_form() {
        $test_post_id = isset( $_GET['test_recipe'] ) ? intval( $_GET['test_recipe'] ) : 0;

        ?>
        <form method="get" action="">
            <input type="hidden" name="page" value="cjc-auto-schema">
            <p>
                <label for="test_recipe">Post ID:</label>
                <input type="number" name="test_recipe" id="test_recipe" value="<?php echo $test_post_id; ?>" style="width: 100px;">
                <input type="submit" class="button" value="Test Recipe Schema">
            </p>
        </form>

        <?php
        if ( $test_post_id ) {
            $post = get_post( $test_post_id );
            if ( ! $post ) {
                echo '<p class="cjc-error">Post not found.</p>';
                return;
            }

            echo '<h4>Testing: ' . esc_html( $post->post_title ) . '</h4>';

            // Check if it's a recipe post
            $categories  = wp_get_post_categories( $post->ID );
            $recipe_cats = array_merge( array( CJC_RECIPE_CATEGORY_ID ), CJC_RECIPE_CHILD_CATEGORIES );
            $is_recipe   = ! empty( array_intersect( $categories, $recipe_cats ) );

            if ( ! $is_recipe ) {
                echo '<p class="cjc-warning">⚠ This post is not in a recipe category.</p>';
            }

            // Parse and show data
            $parser = new CJC_Recipe_Parser( $post );
            $data   = $parser->parse();

            echo '<h4>Parsed Data:</h4>';
            echo '<div class="cjc-schema-preview">';
            echo esc_html( print_r( $data, true ) );
            echo '</div>';

            // Show schema output
            if ( $parser->is_valid_recipe() ) {
                $schema = new CJC_Recipe_Schema( $post );
                echo '<h4>Generated Schema:</h4>';
                echo '<div class="cjc-schema-preview">';
                echo esc_html( wp_json_encode( $schema->generate_schema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
                echo '</div>';
            } else {
                echo '<p class="cjc-error">✗ Cannot generate schema - missing required ingredients or instructions.</p>';
            }
        }
    }

    /**
     * Render FAQ preview form
     */
    private function render_faq_preview_form() {
        $preview_slug = isset( $_GET['preview_faq'] ) ? sanitize_text_field( $_GET['preview_faq'] ) : '';

        ?>
        <form method="get" action="">
            <input type="hidden" name="page" value="cjc-auto-schema">
            <p>
                <label for="preview_faq">Pillar Page:</label>
                <select name="preview_faq" id="preview_faq">
                    <option value="">-- Select a pillar page --</option>
                    <?php
                    foreach ( CJC_PILLAR_PAGE_SLUGS as $slug ) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr( $slug ),
                            selected( $preview_slug, $slug, false ),
                            esc_html( $slug )
                        );
                    }
                    ?>
                </select>
                <input type="submit" class="button" value="Preview FAQ Schema">
            </p>
        </form>

        <?php
        if ( $preview_slug ) {
            $faq_schema = new CJC_FAQ_Schema( $preview_slug );
            $faq_data   = CJC_FAQ_Schema::get_faq_data_by_slug( $preview_slug );

            if ( $faq_data ) {
                echo '<h4>FAQ Questions for "' . esc_html( $preview_slug ) . '":</h4>';
                echo '<ol>';
                foreach ( $faq_data as $faq ) {
                    echo '<li><strong>' . esc_html( $faq['question'] ) . '</strong></li>';
                }
                echo '</ol>';

                echo '<h4>Generated Schema:</h4>';
                echo '<div class="cjc-schema-preview">';
                echo esc_html( wp_json_encode( $faq_schema->generate_schema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
                echo '</div>';
            } else {
                echo '<p class="cjc-error">No FAQ data found for this slug.</p>';
            }
        }
    }
}
