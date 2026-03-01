<?php
/**
 * FAQ Schema Class
 *
 * Generates FAQ JSON-LD schema markup for pillar pages
 *
 * @package CJC_Auto_Schema
 */

defined( 'ABSPATH' ) || exit;

class CJC_FAQ_Schema {

    /**
     * The page slug
     *
     * @var string
     */
    private $page_slug;

    /**
     * The page ID
     *
     * @var int
     */
    private $page_id;

    /**
     * FAQ data for pillar pages, keyed by slug
     * Each page gets 5 Q&A pairs
     *
     * @var array
     */
    private static $faq_data = array(
        // Complete Guide to Hawaiian Poke
        'guide-hawaiian-poke' => array(
            array(
                'question' => 'What is poke?',
                'answer'   => 'Poke (pronounced poh-KAY) is a traditional Hawaiian dish featuring cubed raw fish, typically ahi (yellowfin tuna) or tako (octopus), seasoned with soy sauce, sesame oil, green onions, and sea salt. The word "poke" means "to slice" or "to cut" in Hawaiian, referring to the way the fish is prepared.',
            ),
            array(
                'question' => 'What is the best fish to use for poke?',
                'answer'   => 'The best fish for poke is sushi-grade ahi (yellowfin tuna) due to its firm texture and mild flavor. Other excellent options include salmon, hamachi (yellowtail), and tako (octopus). Always purchase fish labeled "sushi-grade" or "sashimi-grade" from a reputable fishmonger to ensure it\'s safe for raw consumption.',
            ),
            array(
                'question' => 'How long does poke last in the refrigerator?',
                'answer'   => 'Fresh homemade poke is best consumed within 24 hours of preparation. The fish will stay fresh for up to 2 days when stored in an airtight container in the coldest part of your refrigerator. However, the texture may become softer as the acids in the marinade begin to "cook" the fish.',
            ),
            array(
                'question' => 'What is the difference between Hawaiian poke and mainland poke bowls?',
                'answer'   => 'Traditional Hawaiian poke is simple and focused on the fish, using minimal seasonings like soy sauce, sesame oil, and green onions. Mainland-style poke bowls are more elaborate, often served over rice with toppings like avocado, cucumber, edamame, and various sauces. Both are delicious, but they represent different culinary traditions.',
            ),
            array(
                'question' => 'Can poke be made with cooked fish?',
                'answer'   => 'Yes, poke can be made with cooked fish for those who prefer not to eat raw seafood. Cooked shrimp, crab, or seared ahi work well as alternatives. You can also make vegetarian poke using tofu or cubed vegetables like beets or avocado, seasoned with traditional poke marinades.',
            ),
        ),

        // Mastering Hawaiian Plate Lunch
        'guide-plate-lunch' => array(
            array(
                'question' => 'What is a Hawaiian plate lunch?',
                'answer'   => 'A Hawaiian plate lunch is a traditional local meal consisting of two scoops of white rice, one scoop of macaroni salad, and a protein entrée such as kalua pork, loco moco, teriyaki chicken, or katsu. It originated from the plantation era when workers from different cultures shared their foods, creating this iconic Hawaiian comfort food.',
            ),
            array(
                'question' => 'What makes Hawaiian macaroni salad different?',
                'answer'   => 'Hawaiian macaroni salad is creamier and tangier than mainland versions. The key differences are using overcooked, soft macaroni that absorbs the dressing, generous amounts of Best Foods/Hellmann\'s mayonnaise, a splash of apple cider vinegar or milk, and finely grated carrots and onions. It should be made a day ahead for best flavor.',
            ),
            array(
                'question' => 'What are the most popular plate lunch proteins?',
                'answer'   => 'The most popular plate lunch proteins include kalua pork (shredded smoky pork), loco moco (hamburger patty with gravy and egg), chicken katsu (breaded fried chicken cutlet), teriyaki beef or chicken, shoyu chicken, and Spam musubi. Many plate lunch shops also offer combination plates with multiple proteins.',
            ),
            array(
                'question' => 'Why is white rice important in a plate lunch?',
                'answer'   => 'White rice is essential to a plate lunch because it serves as the foundation that absorbs the flavors and gravies from the protein. It reflects Hawaii\'s Asian heritage and plantation history where rice was a dietary staple. The standard serving is always two scoops, never one, and it\'s typically medium-grain Japanese-style rice.',
            ),
            array(
                'question' => 'Where did the plate lunch originate?',
                'answer'   => 'The plate lunch originated in Hawaii during the plantation era of the early 1900s. Workers from different ethnic backgrounds—Japanese, Chinese, Filipino, Portuguese, and Korean—would share their packed lunches, creating a fusion of culinary traditions. The format of protein, rice, and a side became standardized by lunch wagon vendors serving these diverse communities.',
            ),
        ),

        // Essential Hawaiian Ingredients
        'guide-hawaiian-ingredients' => array(
            array(
                'question' => 'What is poi and how is it made?',
                'answer'   => 'Poi is a traditional Hawaiian staple food made from cooked taro root (kalo) that is pounded and mixed with water to create a smooth, paste-like consistency. It has a slightly sour, earthy flavor that develops as it ferments. Poi is considered sacred in Hawaiian culture and is rich in vitamins, minerals, and easily digestible carbohydrates.',
            ),
            array(
                'question' => 'What can I substitute for Hawaiian sea salt?',
                'answer'   => 'If you can\'t find Hawaiian sea salt (alaea salt), you can substitute with coarse sea salt or kosher salt. For the red alaea salt specifically, you can mix regular sea salt with a small amount of red clay powder (food-grade alaea clay). Fleur de sel also works well as a premium alternative for finishing dishes.',
            ),
            array(
                'question' => 'What is li hing mui and how is it used?',
                'answer'   => 'Li hing mui is a dried, salted plum that\'s both sweet, sour, and salty. The powder form is used as a seasoning throughout Hawaii on fresh fruits like pineapple and mango, candies, shave ice, and even on the rim of cocktails. It\'s a uniquely Hawaiian-Chinese flavor profile that locals grow up enjoying.',
            ),
            array(
                'question' => 'Where can I buy Hawaiian ingredients online?',
                'answer'   => 'Hawaiian ingredients can be purchased online from specialty retailers like Hawaii\'s Best, Maui\'s Best, ABC Stores online, and Amazon. For items like poi, taro, and fresh Hawaiian fish, check Hawaiian Airlines\' Hawaiian Miles Marketplace or local Hawaiian grocery stores that ship nationwide. Asian supermarkets often carry items like furikake and nori.',
            ),
            array(
                'question' => 'What is the difference between shoyu and soy sauce?',
                'answer'   => 'Shoyu is the Japanese word for soy sauce, and in Hawaii, it specifically refers to Japanese-style soy sauce like Aloha Shoyu or Kikkoman. Hawaiian recipes typically use shoyu rather than Chinese-style soy sauce because of the Japanese influence on local cuisine. Shoyu tends to be less salty and more balanced than some other soy sauce varieties.',
            ),
        ),

        // Hawaiian Breakfast Guide
        'hawaiian-breakfast-guide' => array(
            array(
                'question' => 'What is a traditional Hawaiian breakfast?',
                'answer'   => 'A traditional Hawaiian breakfast often includes rice instead of toast, Portuguese sausage, eggs, and Spam. Popular items include loco moco (rice topped with hamburger patty, gravy, and fried egg), Portuguese sweet bread French toast, Hawaiian-style pancakes with coconut syrup, and fresh tropical fruits like papaya and pineapple.',
            ),
            array(
                'question' => 'Why is Spam so popular in Hawaii?',
                'answer'   => 'Spam became popular in Hawaii during World War II when fresh meat was scarce and the canned product was sent to feed troops stationed on the islands. Locals embraced it as a protein source, and it became integrated into Hawaiian cuisine. Today, Hawaii consumes more Spam per capita than any other state, enjoying it in musubi, breakfast dishes, and plate lunches.',
            ),
            array(
                'question' => 'What is Portuguese sausage?',
                'answer'   => 'Portuguese sausage (linguiça) is a mildly spiced, garlicky pork sausage brought to Hawaii by Portuguese immigrants in the late 1800s. It\'s a breakfast staple throughout the islands, typically served sliced and pan-fried alongside eggs and rice. The most popular local brand is Redondo\'s, known for its slightly sweet and smoky flavor.',
            ),
            array(
                'question' => 'How do you make Hawaiian-style eggs?',
                'answer'   => 'Hawaiian-style eggs are typically fried sunny-side up or over-easy to allow the runny yolk to mix with rice and other breakfast items. For local-style scrambled eggs, they\'re often cooked with Spam, Portuguese sausage, char siu, or vegetables. The eggs are seasoned simply with salt and pepper, letting the accompanying flavors shine.',
            ),
            array(
                'question' => 'What is malasada?',
                'answer'   => 'Malasadas are Portuguese-style fried doughnuts without holes, brought to Hawaii by Madeiran immigrants in the 19th century. They\'re made from an eggy, yeasted dough that\'s deep-fried and coated in sugar while still warm. Modern Hawaiian bakeries fill them with haupia (coconut pudding), custard, or tropical fruit fillings like guava and lilikoi.',
            ),
        ),

        // Island Drinks Guide
        'hawaiian-drinks-guide' => array(
            array(
                'question' => 'What is a Mai Tai and where was it invented?',
                'answer'   => 'The Mai Tai is a rum-based cocktail with disputed origins, claimed by both Trader Vic\'s in Oakland (1944) and Don the Beachcomber in Hollywood. The original recipe combines aged rum, lime juice, orange curaçao, orgeat syrup, and simple syrup. In Hawaii, the Mai Tai has evolved with local variations using tropical juices like pineapple and orange.',
            ),
            array(
                'question' => 'What is POG juice?',
                'answer'   => 'POG juice is a popular Hawaiian drink made from a blend of Passionfruit, Orange, and Guava (hence "POG"). It was created in 1971 by Mary Soon, a food service director in Maui. The sweet, tangy, tropical flavor became so iconic that it\'s now considered quintessentially Hawaiian and is enjoyed as a juice, in cocktails, and as a flavoring for various treats.',
            ),
            array(
                'question' => 'How do you make authentic Hawaiian shave ice?',
                'answer'   => 'Authentic Hawaiian shave ice uses finely shaved (not crushed) ice with a snow-like texture that absorbs syrup perfectly. It\'s topped with flavored syrups like li hing mui, lilikoi, or coconut. Traditional additions include a scoop of vanilla ice cream or azuki beans at the bottom, and condensed milk ("snow cap") drizzled on top.',
            ),
            array(
                'question' => 'What non-alcoholic Hawaiian drinks are popular?',
                'answer'   => 'Popular non-alcoholic Hawaiian drinks include fresh coconut water straight from young coconuts, lilikoi (passion fruit) juice, guava nectar, Hawaiian Sun fruit drinks, and smoothies made with açaí, mango, or pineapple. Locals also enjoy unique drinks like grass jelly tea, calamansi juice, and various boba tea flavors.',
            ),
            array(
                'question' => 'What is Kona coffee and why is it special?',
                'answer'   => 'Kona coffee is grown exclusively on the slopes of Hualalai and Mauna Loa on Hawaii\'s Big Island. The unique microclimate with morning sun, afternoon clouds, and mineral-rich volcanic soil creates beans with a smooth, rich flavor and low acidity. True 100% Kona coffee is premium-priced due to limited growing area and hand-harvesting requirements.',
            ),
        ),

        // Hawaiian Desserts Guide
        'hawaiian-desserts-guide' => array(
            array(
                'question' => 'What is haupia?',
                'answer'   => 'Haupia is a traditional Hawaiian coconut pudding made from coconut milk, sugar, and a thickening agent (traditionally Polynesian arrowroot, now often cornstarch). It has a firm, jiggly texture similar to gelatin and is served cut into squares. Haupia is a staple at luaus and a popular topping for desserts, especially haupia chocolate pie.',
            ),
            array(
                'question' => 'What is a butter mochi?',
                'answer'   => 'Butter mochi is a popular Hawaiian dessert combining Japanese mochi (glutinous rice flour) with butter, eggs, sugar, coconut milk, and vanilla. The result is a chewy, dense cake with a slightly crispy top and soft, stretchy interior. It reflects Hawaii\'s blend of Japanese and local influences and is a favorite at potlucks and bake sales.',
            ),
            array(
                'question' => 'What flavors are used in Hawaiian desserts?',
                'answer'   => 'Hawaiian desserts feature tropical flavors like coconut, lilikoi (passion fruit), guava, mango, pineapple, and macadamia nuts. Unique local flavors include ube (purple yam), haupia (coconut pudding), li hing mui (preserved plum), and taro. These flavors appear in everything from cakes and pies to shave ice and mochi.',
            ),
            array(
                'question' => 'What is the difference between mochi and butter mochi?',
                'answer'   => 'Traditional mochi is a Japanese rice cake made from pounded glutinous rice with a very chewy, stretchy texture. Butter mochi is a Hawaiian adaptation that adds Western baking ingredients—butter, eggs, sugar, and baking powder—to mochiko flour, creating a dense, chewy cake that\'s baked rather than steamed or pounded.',
            ),
            array(
                'question' => 'How do you make a chantilly cake?',
                'answer'   => 'Chantilly cake, popularized by Hawaiian bakeries, features light sponge cake layers filled and frosted with fresh whipped cream (chantilly cream) and often includes fresh fruit like strawberries. The key is using heavy whipping cream stabilized with a small amount of sugar and vanilla, beaten to soft peaks for a light, not-too-sweet frosting.',
            ),
        ),

        // Hawaiian Pupus Guide
        'hawaiian-pupus-guide' => array(
            array(
                'question' => 'What are pupus?',
                'answer'   => 'Pupus (pronounced poo-poos) are Hawaiian appetizers or snacks, similar to hors d\'oeuvres or tapas. The term comes from the Hawaiian word for "shell" and originally referred to small dishes served with drinks. Popular pupus include Spam musubi, poke, chicken wings, edamame, gyoza, and various fried items served at parties and gatherings.',
            ),
            array(
                'question' => 'How do you make Spam musubi?',
                'answer'   => 'To make Spam musubi, slice Spam into rectangles and pan-fry until caramelized. Prepare sushi rice seasoned with rice vinegar. Using a musubi mold (or the Spam can), layer rice, then Spam, and wrap with a strip of nori seaweed. The classic version includes teriyaki glaze on the Spam, though variations add egg, furikake, or other toppings.',
            ),
            array(
                'question' => 'What is a pupu platter?',
                'answer'   => 'A pupu platter is an assortment of Hawaiian appetizers served together on a large plate or tray, perfect for sharing at parties. Traditional items include teriyaki beef sticks, chicken wings, fried wontons, spring rolls, edamame, poke, and various dipping sauces. It\'s similar to a combination appetizer but with distinctly Hawaiian-Asian flavors.',
            ),
            array(
                'question' => 'What are the best pupus for a luau?',
                'answer'   => 'Essential luau pupus include fresh poke (ahi or tako), Spam musubi, teriyaki chicken skewers, coconut shrimp, lumpia (Filipino spring rolls), haupia for dessert, and fresh tropical fruit. For larger gatherings, add kalua pork sliders, Hawaiian-style chicken wings, and edamame. Balance flavors between savory, sweet, and fresh items.',
            ),
            array(
                'question' => 'What dipping sauces go with Hawaiian pupus?',
                'answer'   => 'Popular dipping sauces for Hawaiian pupus include shoyu (soy sauce) mixed with ginger, sweet chili sauce, teriyaki glaze, spicy mayo (mayonnaise with sriracha), and ponzu. For fried items, a simple mix of shoyu, rice vinegar, and sesame oil works well. Wasabi and pickled ginger are classic accompaniments for poke and sushi-style items.',
            ),
        ),
    );

    /**
     * Constructor
     *
     * @param string $page_slug The page slug
     * @param int    $page_id   The page ID (optional)
     */
    public function __construct( $page_slug, $page_id = 0 ) {
        $this->page_slug = $page_slug;
        $this->page_id   = $page_id;
    }

    /**
     * Check if this page should have FAQ schema
     *
     * @return bool
     */
    public function should_output_schema() {
        // Check if this is a pillar page by slug
        if ( ! in_array( $this->page_slug, CJC_PILLAR_PAGE_SLUGS, true ) ) {
            return false;
        }

        // Check if we have FAQ data for this page
        if ( ! isset( self::$faq_data[ $this->page_slug ] ) ) {
            return false;
        }

        // Check if Rank Math is already handling FAQ schema
        if ( $this->page_id > 0 ) {
            $rank_math_schema = get_post_meta( $this->page_id, 'rank_math_rich_snippet', true );
            if ( $rank_math_schema === 'faq' ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate the FAQ schema array
     *
     * @return array Schema data
     */
    public function generate_schema() {
        $faqs = self::$faq_data[ $this->page_slug ];

        $main_entity = array();
        foreach ( $faqs as $faq ) {
            $main_entity[] = array(
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ),
            );
        }

        return array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $main_entity,
        );
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

    /**
     * Generate FAQ schema JSON-LD from post meta
     *
     * Reads _cjc_faq_schema meta (JSON string of Q&A pairs) and returns
     * a complete <script type="application/ld+json"> tag.
     *
     * @param int $post_id The post or page ID
     * @return string JSON-LD script tag, or empty string if no valid data
     */
    public static function generate_schema_from_meta( $post_id ) {
        $raw = get_post_meta( $post_id, '_cjc_faq_schema', true );
        if ( empty( $raw ) ) {
            return '';
        }

        $faqs = json_decode( $raw, true );
        if ( ! is_array( $faqs ) || empty( $faqs ) ) {
            return '';
        }

        $main_entity = array();
        foreach ( $faqs as $faq ) {
            if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
                continue;
            }
            $main_entity[] = array(
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ),
            );
        }

        if ( empty( $main_entity ) ) {
            return '';
        }

        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $main_entity,
        );

        return '<script type="application/ld+json">' . "\n" .
               wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" .
               '</script>' . "\n";
    }

    /**
     * Get FAQ data for a specific page by slug (for admin preview)
     *
     * @param string $page_slug The page slug
     * @return array|null FAQ data or null if not found
     */
    public static function get_faq_data_by_slug( $page_slug ) {
        return isset( self::$faq_data[ $page_slug ] ) ? self::$faq_data[ $page_slug ] : null;
    }

    /**
     * Get all pillar page slugs with FAQ data
     *
     * @return array Page slugs
     */
    public static function get_pillar_page_slugs() {
        return array_keys( self::$faq_data );
    }
}
