jQuery(function ($) {

    $(document).ready(function () {

        [].slice.call(document.querySelectorAll('.tabs-jarvis')).forEach(function (el) {
            new CBPFWTabs(el);
        });
        //JarvisGrid start
        $('#jarvisGrid').gridEditor({
            new_row_layouts: [[12], [6, 6], [4, 4, 4], [3, 3, 3, 3], [2, 2, 2, 2, 2, 2]],
            content_types: ['summernote']
        });

        // Get resulting html
        $("#saveGrid").click(function () {
            var html = $('#jarvisGrid').gridEditor('getHtml');
            console.log(html);
            $('#qcld_jarvis_front_tags').val(html);

        });
        $("#resetGrid").click(function () {
            $('#jarvisGrid').html('');
            $('#qcld_jarvis_front_tags').val('');

        });

        //JarvisGrid end


        $(".jarvis_select_two").select2({width: '1100px', height: '500px', dropdownCssClass: "bigdrop"});

        if ($(".jarvis_sentence_builder").length) {

            $(".jarvis_sentence_builder .repeatable").repeatable({
                addTrigger: ".jarvis_sentence_builder .add",
                deleteTrigger: ".jarvis_sentence_builder .delete",
                template: "#jarvis_sentence_builder",
                startWith: 1,
                onDelete: function () {
                    build_search_phrase();
                }
            });

            $(".jarvis_sentence_builder").on("change", ".qc-jarvis-filter", function () {
                if ($(this).val() == "price") {
                    $(this).parents(".field-group:eq(0)").find(".price-set").removeClass('qc-jarvis-hide');
                    $(this).parents(".field-group:eq(0)").find(".label-set").addClass('qc-jarvis-hide');
                } else {
                    $(this).parents(".field-group:eq(0)").find(".price-set").addClass('qc-jarvis-hide');
                    $(this).parents(".field-group:eq(0)").find(".label-set").removeClass('qc-jarvis-hide');
                }
            });

            $(".jarvis_sentence_builder .repeatable").sortable({
                items: "> div.field-group", cursor: "move", stop: function (event, ui) {
                    build_search_phrase();
                }
            });
            $(".jarvis_sentence_builder .repeatable");

        }


        // Setup the tips
        jQuery(".tips, .help_tip").tipTip({
            attribute: "data-tip",
            fadeIn: 50,
            fadeOut: 50,
            delay: 200,
            defaultPosition: "top"
        });

        build_search_phrase();

        $(".jarvis_sentence_builder .repeatable").on("keyup", ".field-group .qc-jarvis-text", build_search_phrase);
        $(".jarvis_sentence_builder .repeatable").on("change", ".field-group .qc-jarvis-filter", build_search_phrase);
        $(".jarvis_sentence_builder .repeatable").on("keyup", ".field-group .jarvis-priceone", build_search_phrase);
        $(".jarvis_sentence_builder .repeatable").on("keyup", ".field-group .jarvis-pricetwo", build_search_phrase);
        $(".jarvis_sentence_builder .repeatable").on("keyup", ".field-group .qc-jarvis-label", build_search_phrase);
        $(".shop-jarvis-button-text").on("keyup", build_search_phrase);

    });

    function build_search_phrase() {
        var output = "";

        $(".jarvis_sentence_builder .repeatable .field-group").each(function () {

            var text = $(this).find(".qc-jarvis-text").val();

            if (text != "") {
                output += text + " ";
            }

            var filter = $(this).find(".qc-jarvis-filter").val();


            if (filter != "") {

                var priceone = $(this).find(".jarvis-priceone").val();
                var pricetwo = $(this).find(".jarvis-pricetwo").val();
                var label = $(this).find(".qc-jarvis-label").val();

                if (filter == "price") {

                    output += '<span class="phrase-example-filter">' + priceone + '</span> & <span class="phrase-example-filter">' + pricetwo + '</span> ';

                } else {

                    output += '<span class="phrase-example-filter">' + label + "</span> ";

                }

            }

        });

        if (output != "") {
            output = output.substring(0, output.length - 1);

            var button_text = $(".jarvis-search-button-text").val();

            if (button_text != "") {
                output += '. <span class="phrase-example-button">' + button_text + '</span>';
            } else {
                output += '. <span class="phrase-example-button"></span>';
            }

            $(".search-phrase").html(output);
            $(".search-phrase").show();
            $(".pre-search-phrase").hide();
        } else {
            $(".pre-search-phrase").show();
            $(".search-phrase").hide();
        }

    }

});