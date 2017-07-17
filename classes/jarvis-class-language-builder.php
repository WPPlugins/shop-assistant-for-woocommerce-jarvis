<?php

class LanguageBuilder
{

    public static function get_template()
    {
        global $woocommerce;
        $woo_attributes = wc_get_attribute_taxonomies();
        $woo_categories = get_terms("product_cat", array("hide_empty" => 0));
        $woo_tags = get_terms("product_tag", array("hide_empty" => 0));

        $filter = "";

        $filter .= '<div class="field-group row controls-row" id="field_group_{?}">';

        $filter .= '	<div class="col-sm-4"><div class="field-group-set">';
        $filter .= '		<input type="text" class="qc-jarvis-text" name="jarvis-terms-fields[{?}][text]" value="{text}" id="text_{?}">';
        $filter .= '	</div></div>';

        $filter .= '	<div class="col-sm-4"><div class="field-group-set">';
        $filter .= "		<select class='qc-jarvis-filter' name='jarvis-terms-fields[{?}][filter]' id='jarvis-terms-fields[{?}][filter]'>";
        $filter .= "			<option value='' selected='selected' >" . __("Options", "jarvis") . "</option>";
        $filter .= "			<option value='price' >Price</option>";
        if ($woo_categories) {
            $filter .= "		<option value='product_cat' >" . __("Product Categories", "jarvis") . "</option>";
        }
        if ($woo_tags) {
            $filter .= "		<option value='product_tag' >" . __("Product Tags", "jarvis") . "</option>";
        }
        if ($woo_attributes) {
            foreach ($woo_attributes as $attribute) {
                $filter .= "	<option value='" . $attribute->attribute_id . "' >" . $attribute->attribute_label . "</option>";
            }
        }
        $filter .= "		</select>";
        $filter .= "	</div></div>";

        $filter .= '	<div class="col-sm-4"><div class="field-group-set price-set qc-jarvis-hide">';
        $filter .= '		<span class="number-set">';
        $filter .= '			<input type="text" class="jarvis-priceone" name="jarvis-terms-fields[{?}][priceone]" value="{priceone}" id="priceone_{?}">';
        $filter .= '		</span>';
        $filter .= '		<span class="number-set">';
        $filter .= '			<input type="text" class="jarvis-pricetwo" name="jarvis-terms-fields[{?}][pricetwo]" value="{pricetwo}" id="pricetwo_{?}">';
        $filter .= '		</span>';
        $filter .= '	</div></div>';

        $filter .= '	<div class="col-sm-4"><div class="field-group-set label-set">';
        $filter .= '		<input type="text" class="qc-jarvis-label" name="jarvis-terms-fields[{?}][label]" value="{label}" id="label_{?}">';
        $filter .= '	</div></div>';

        $filter .= '	<span class="action-buttons delete">' . __("Delete", "shop-jarvis") . '</span>';

        $filter .= '	<span class="action-buttons handle reorder">' . __("Reorder", "shop-jarvis") . '</span>';


        $filter .= '</div>';

        return $filter;
    }

    public static function repopulate()
    {
        global $woocommerce;

        $form_fields = get_option('jarvis-terms-fields');

        if (empty($form_fields)) {
            return;
        } else {
            $form_fields = unserialize($form_fields);
        }
        $i = 0;
        foreach ($form_fields as $form_field) {
            $formatted_template = preg_replace("/\{\?\}/", $i++, LanguageBuilder::get_template());
            $filter_value = false;
            foreach ($form_field as $k => $v) {
                $formatted_template = preg_replace("/\{{$k}\}/", htmlentities($v, ENT_QUOTES, "UTF-8"), $formatted_template);
                if ($k == "filter") {
                    $filter_value = $v;
                }
            }
            echo $formatted_template;
            $j = $i - 1;
            if ($filter_value == "price") {
                wc_enqueue_js("jQuery('#field_group_" . $j . "').find('.price-set').removeClass('qc-jarvis-hide');");
                wc_enqueue_js("jQuery('#field_group_" . $j . "').find('.label-set').addClass('qc-jarvis-hide');");
            }
            wc_enqueue_js("jQuery('#field_group_" . $j . "').find('.qc-jarvis-filter').val('" . $filter_value . "');");

        }
    }
}