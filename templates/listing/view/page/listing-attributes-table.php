<?php

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined('ABSPATH') || exit;

if (hp\is_field_specified($listing, 'view_page_table')) : ?>
    <div class="hp-listing__attributes hp-listing__attributes--table">
        <div class="hp-row">
            <?php
            foreach ($listing->_get_fields('view_page_table') as $field) :
                if (!is_null($field->get_value())) :
                    ?>
                    <div class="hp-col-lg-12 hp-col-xs-12">
                        <div class="hp-listing__attribute hp-listing__attribute--<?php echo esc_attr(hivepress()->helper->sanitize_slug($field->get_name())); ?>">
                            <div class="hp-listing__attribute table-wrapper">
                                <?php echo $field->display(); ?>
                            </div>
                        </div>
                    </div>
                <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
<?php endif; ?>
