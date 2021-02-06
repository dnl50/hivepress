<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->_get_fields( 'view_page_tertiary' ) ) :
    ?>
    <div class="hp-listing__attributes hp-listing__attributes--tertiary">
        <div class="hp-row">
            <?php
            foreach ( $listing->_get_fields( 'view_page_tertiary' ) as $field ) :
                if ( ! is_null( $field->get_value() ) ) :
                    ?>
                    <div class="hp-col-lg-6 hp-col-xs-12">
                        <div class="hp-listing__attribute hp-listing__attribute--<?php echo esc_attr( hivepress()->helper->sanitize_slug( $field->get_name() ) ); ?>">
                            <?php echo $field->display(); ?>
                        </div>
                    </div>
                <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
<?php
endif;
