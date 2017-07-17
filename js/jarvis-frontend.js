jQuery(function ($) {

    $(function () {
        $('ul.jarvis_product_list').slimScroll({
            height: '319px'
        });
        $('.qcld_cart_prod_table_body').slimScroll({
            height: '270px'
        });
    });


    var data = {
        'action': 'get_cart_products'
    };

    jQuery.post(ajax_object.ajax_url, data, function (response) {
        //console.log('Got this from the server: ' + response);
    });

    $('.jarvis-find').on("click", function () {
        var jarvis = $(this).parents('.woocommerce-jarvis:eq(0)');
        update_search_link(jarvis);
    });

    $('.jarvis-find').on("click", function () {
        var jarvis = $(this).parents('.woocommerce-jarvis:eq(0)');
        update_search_link(jarvis);
    });

    //});

    function update_search_link(jarvis) {
        var current_shop_url = qc_jarvis_params.shop_url;
        var product_cats = "";
        var product_tags = "";
        var product_atts = "";

        var query = "?";
        if (current_shop_url.indexOf("?") != -1) {
            query = "&";
        }

        // Product Categories
        if (jarvis.find('.jarvis-field-type-product-category').length) {
            jarvis.find('.jarvis-field-type-product-category').each(function () {
                if ($(this).find(".jarvis-select li.selected").length) {
                    if ($(this).find(".jarvis-select li:not(:first).selected a").attr("data-value")) {
                        product_cats += $(this).find(".jarvis-select li.selected a").attr("data-value") + ",";
                    }
                }
            });
            if (product_cats != "") {
                product_cats = product_cats.substring(0, product_cats.length - 1);
                query += "sa_product_cat=" + product_cats + "&";
            }
        }

        // Product Tags
        if (jarvis.find('.jarvis-field-type-product-tag').length) {
            jarvis.find('.jarvis-field-type-product-tag').each(function () {
                if ($(this).find(".jarvis-select li:not(:first).selected").length) {
                    product_tags += $(this).find(".jarvis-select li.selected a").attr("data-value") + ",";
                }
            });
            if (product_tags != "") {
                product_tags = product_tags.substring(0, product_tags.length - 1);
                query += "sa_product_tag=" + product_tags + "&";
            }
        }

        // Product Min Price
        if (jarvis.find('input[name="sa_min_price"]').length) {
            var sa_min_price = jarvis.find('input[name="sa_min_price"]').val();
            query += "sa_min_price=" + sa_min_price + "&";
        }

        // Product Max Price
        if (jarvis.find('input[name="sa_max_price"]').length) {
            var sa_max_price = jarvis.find('input[name="sa_max_price"]').val();
            query += "sa_max_price=" + sa_max_price + "&";
        }

        // Product Attributes
        if (jarvis.find('.jarvis-field-type-attribute').length) {
            jarvis.find('.jarvis-field-type-attribute').each(function () {
                if ($(this).find(".jarvis-select li:not(:first).selected").length) {
                    var attribute_name = $(this).find(".jarvis-select").attr("data-name");
                    var attribute_value = $(this).find(".jarvis-select li.selected a").attr("data-value");
                    query += attribute_name + "=" + attribute_value + "&";
                }
            });
        }

        if (( query != "?" ) && ( query != "&" )) {
            query = query.substring(0, query.length - 1);
            current_shop_url += query;

            window.location = current_shop_url;
        } else {
            return false;
        }

    }


    //Fade in button
    jQuery(".woocommerce-jarvis").hover(
        function (event) {
            var this_jarvis = jQuery(this);
            var find_button = jQuery(this).find(".jarvis-find");

            if (!this_jarvis.hasClass('jarvis-has-activated')) {

                // Animate the find button once
                find_button.stop(true, true).animate({
                    top: "-8px",
                    opacity: 0,
                }, 100, 'easeInOutQuad').animate({top: "8px", opacity: 0}, 1).delay(100).animate({
                    top: "0px",
                    opacity: 1
                }, 100, 'easeInOutQuad');

                this_jarvis.addClass('jarvis-has-activated');
            }
            event.preventDefault();
        },
        function (event) {
            event.preventDefault();
        }
    );


    //Set Width's of DropDowns
    jQuery(".jarvis-field ul").each(function () {
        jQuery(this).css({marginLeft: -jQuery(this).width() / 2});
    });


    //Easing Functions
    jQuery.easing.easeInQuad = function (x, t, b, c, d) {
        return c * (t /= d) * t + b;
    };
    jQuery.easing.easeOutQuad = function (x, t, b, c, d) {
        return -c * (t /= d) * (t - 2) + b;
    };
    jQuery.easing.easeInOutQuad = function (x, t, b, c, d) {
        if ((t /= d / 2) < 1) return c / 2 * t * t + b;
        return -c / 2 * ((--t) * (t - 2) - 1) + b;
    };


    //DropDown Open
    jQuery('.jarvis-field > a').on('click', function (event) {
        event.preventDefault();
        var ullist = jQuery(this).parent().children('ul:first');

        ullist.slideDown(500, 'easeInOutQuad').animate({top: "-20px"}, {
            queue: false,
            duration: 500,
            easing: 'easeInOutQuad'
        });

        jarvis_close_fields(jQuery(".jarvis-field").not(jQuery(event.target).parents(".jarvis-field")));

        event.stopPropagation();
    });

    //DropDown Select
    jQuery('.jarvis-field li a').on('click', function (event) {
        event.preventDefault();

        var current_jarvis = jQuery(this).parents('.woocommerce-jarvis');
        var current_field = jQuery(this).parents('.jarvis-field');
        var current_field_display_text = current_field.find('a:first');
        var clicked_element = jQuery(this).parent('li');
        var other_elements = jQuery(this).parents("ul").find('li').not(clicked_element);
        var reset_element = current_jarvis.find(".jarvis-reset");

        //Set the class of the clicked element
        clicked_element.addClass("selected");
        other_elements.removeClass("selected");

        current_field.attr('data-new-value', clicked_element.find('a').html());

        //Do the lock in animation
        current_field_display_text.delay(250).animate({top: "-10px", opacity: 0}, 150).animate({
            top: "10px",
            opacity: 0
        }, 1, function () {

            //Set the value of the top-most visible text
            current_field_display_text.html(current_field.attr('data-new-value'));

            //Set the value of the dropdown
            current_value = clicked_element.find('a').attr("data-value");
            if (current_value === "" || current_value == "any" || current_value === undefined)
                current_field.find('select').val("");
            else
                current_field.find('select').val(current_value);

        }).animate({top: "0px", opacity: 1}, 150);

        //Close all fields on select of something
        jarvis_close_fields(jQuery('.jarvis-field'));

        //switch on the reset button
        current_jarvis.addClass("jarvis-active");

        event.stopPropagation();
    });

    //Close all on click outside
    jQuery(document).click(function (event) {
        //Close the feilds of all the jarviss except the current one
        jarvis_close_fields(jQuery(".jarvis-field").not(jQuery(event.target).parents(".jarvis-field")));
    });

    //Function to close all
    function jarvis_close_fields(elements) {
        elements.each(function () {
            jQuery(this).find('ul').slideUp(300, 'easeInOutQuad').animate({top: "0"}, {
                queue: false,
                duration: 300,
                easing: 'easeInOutQuad'
            });
        });
    }

    //Clear Button Click
    jQuery('.woocommerce-jarvis .jarvis-reset').on('click', function (event) {

        var current_jarvis = jQuery(this).parents('.woocommerce-jarvis');
        var reset_element = current_jarvis.find("jarvis-reset");

        current_jarvis.find('.jarvis-field').each(function () {

            if (jQuery(this).hasClass('jarvis-field-select')) {

                if (jQuery(this).find('ul li.original').length) {
                    jQuery(this).find('ul li.original').find('a').click();
                }
                else {
                    jQuery(this).find('ul li').first().find('a').click();
                    jQuery(this).attr("data-new-value", jQuery(this).attr('data-original-value'));
                }
            }
            else if (jQuery(this).hasClass('jarvis-field-input')) {

                jQuery(this).find('input').each(function () {

                    reset_value = jQuery(this).attr('data-original-value');
                    jQuery(this).val(reset_value);

                });

            }

        });

        //switch on the reset button
        current_jarvis.removeClass("jarvis-active");

        event.stopPropagation();
    });


    $("#jarvis_body .add_to_cart_button").each(function (index, element) {
        var prodlink = $(this).parent().find(".woocommerce-LoopProduct-link").attr('href');
        $(this).attr("href", prodlink);
        $(this).removeClass("ajax_add_to_cart");
        $(this).text("View Detail");

    });

    $("#genie-lamp").animatedModal({
        modalTarget: 'genie-target',
        animatedIn: 'lightSpeedIn',
        animatedOut: 'bounceOutDown',
        color: '#eef5f9',
        // Callbacks
        beforeOpen: function () {
            //console.log("The animation was called");
        },
        afterOpen: function () {
            // console.log("The animation is completed");
        },
        beforeClose: function () {
            // console.log("The animation was called");
        },
        afterClose: function () {
            //console.log("The animation is completed");
        }
    });

    $("#genie-lamp").hover(function () {
        $("#qcld_jarvis_msg li").removeClass("active");
        $("#qcld_jarvis_msg li:first-child").addClass("active");
        $("#qcld_jarvis_tooltip").addClass("active");
    }, function () {
        $("#qcld_jarvis_tooltip").removeClass("active");
        //hideJarvisMessage();

    });


    //setInterval(function(){ alert("Hello"); }, 3000);

	//
	//var msgTotal = 3;
	var globalTimer = $("#qcld_jarvis_msg").attr("data-global-timer");
	var msgLoop = 0;
	setTimeout(function () {
        changeMessage();
    }, 2000)

    

    if(globalTimer<1){
        globalTimer = 8;
    }
   
   var runningMsg = 0;
    var showMessages = setInterval(function () {
		runningMsg++;
		msgTotal = 3;
        //$("#qcld_jarvis_tooltip").toggleClass("active");
        //hideJarvisMessage();
        changeMessage();
    }, globalTimer*1000)



/*    function hideJarvisMessage() {
        var activeIndex = $("#qcld_jarvis_msg li.active").index("#qcld_jarvis_msg li");
        
		
		if (activeIndex == 4) {
            var nextIndex = 0;
        } else {
            var nextIndex = activeIndex + 1;
        }

        //console.log("activeIndex="+activeIndex+" | nextIndex="+nextIndex);
        $("#qcld_jarvis_msg li").removeClass("active");
        $("#qcld_jarvis_msg li").eq(nextIndex).addClass("active");
    }*/
	
	
	function changeMessage() {
        
		//var msgLoop = $("#qcld_jarvis_tooltip").attr("data-loop");
		var msgLoop = 0;
		var msgTotal = $("#qcld_jarvis_msg > li").size();
		var activeMsgFromCookey = parseInt($.cookie('activeMsg'));
		//console.log("runningMsg "+ activeMsgFromCookey+" out of "+msgTotal);
		if(msgLoop==0 && activeMsgFromCookey == msgTotal ){clearInterval(showMessages); $("#qcld_jarvis_tooltip").removeClass("active"); return false }
		
		
		
		
		//if(msgLoop==0 && runningMsg == msgTotal ){clearInterval(showMessages)}
		//activeMsgFromCookey = parseInt(activeMsgFromCookey+1);
		var activeIndex, nextIndex;
		var msgTotal = $("#qcld_jarvis_msg > li.jarvisMsgItem").size();
		
		//console.log(msgTotal+"total");
		$("#qcld_jarvis_msg > li.jarvisMsgItem").eq(activeMsgFromCookey).addClass("active");
		
		//console.log(activeMsgFromCookey);
		
		if($("#qcld_jarvis_tooltip").hasClass('active')){
			$("#qcld_jarvis_tooltip").toggleClass("active");
		}else{
				activeIndex = $("#qcld_jarvis_msg > li.jarvisMsgItem.active").index("#qcld_jarvis_msg > li");
			if (activeIndex == msgTotal-1) {
				nextIndex = 0;
			} else {
				nextIndex = activeIndex + 1;
			}
			
			nextIndex = activeIndex + 1;
			
			//console.log("activeIndex="+activeIndex+" | nextIndex="+nextIndex);
			$("#qcld_jarvis_msg > li.jarvisMsgItem").removeClass("active");
			$("#qcld_jarvis_msg > li.jarvisMsgItem").eq(nextIndex).addClass("active");
			
			$.cookie('activeMsg',nextIndex, { path:'/'});
			
			$("#qcld_jarvis_tooltip").toggleClass("active");
			
			
			
			
			var activeMsgFromCookey2 = parseInt($.cookie('activeMsg'));
		//console.log("runningMsg2 "+activeMsgFromCookey2+" out of "+msgTotal);
		if(msgLoop==0 && activeMsgFromCookey2 == msgTotal ){clearInterval(showMessages); $("#qcld_jarvis_tooltip").toggleClass("active"); }
			
		}
	
	
		
		
	
		
    }
	


})