jQuery(document).ready(function($) {
   // jQuery("#post-cat option:first-child").attr("selected", true);
   jQuery('.tab-content:nth-child(2)').addClass('firstelement');
   var sCounter = jQuery('#accordion div:last-child').find('.fullshortcode').attr('id');
    jQuery('#la-saved').hide();
    jQuery('#la-loader').hide();
   // console.log(sCounter);
   var icons = {
        header: "dashicons dashicons-plus",
        activeHeader: "dashicons dashicons-no"
    };
   jQuery( "#accordion" ).accordion({  
      collapsible: true,
      icons: icons,
      // header: '.ui-accordion-header-icon'
    });
   
   jQuery('.post-cat').on('change',  function(event) {

        var data = {
            action : 'la_get_terms',
            tax :jQuery( this ).val(),
        }
        console.log(data);
        jQuery( ".post-cat" ).closest('.form-table').find('.get-terms').html('<img src="'+laAjax.path+'images/ajax-loader.gif">');
       jQuery.post(laAjax.url,data, function(resp) {
           jQuery( ".post-cat option:selected" ).closest('.form-table').find('.get-terms').html(resp);
           // console.log(resp);
       });
   });

   jQuery('.my-colorpicker').wpColorPicker(); 
    jQuery('#compactviewer').on('click','.save-meta',function(event) {
        event.preventDefault();
        jQuery('#la-saved').hide();
        jQuery('#la-loader').show();
        var allPost = [];
        jQuery('#accordion > div').each(function(index) {
            var that = jQuery(this);
          var posts = {};

            // console.log(that);
          posts.postCat = jQuery(this).find( ".post-cat option:selected" ).val(),
          posts.term =    jQuery(this).find('.la-term').val(),
          posts.exclude_ids =  jQuery(this).find('.exclude-ids').val(),
          posts.color_val =  jQuery(this).find('.my-colorpicker').val(),
          posts.btntitle = jQuery(this).find('.btntext').val(),
          posts.pvTitle =  jQuery(this).find('.pvtitle').val(),
          posts.pvWidth =  jQuery(this).find('.pvwidth').val(),
          posts.shortcode = that.find('.fullshortcode').attr('id');
          posts.counter = that.find('.fullshortcode').attr('id');

        allPost.push(posts);
        });
        // console.log(allPost);

        var data = {
            action : 'la_save_post_viewer',
             posts : allPost       
        } 

        // console.log(data);
        jQuery.post(laAjax.url, data, function(resp) {
            jQuery('#la-saved').show();
            jQuery('#la-loader').hide();
             jQuery('#la-saved').delay(2000).fadeOut();
        });
    });

      
    jQuery('#accordion .btnadd').click(function(event) {
        sCounter++;
        jQuery('#accordion').append('<h3>Post Viewer</h3>');
        var parent_newly = jQuery(this).closest('.ui-accordion-content').clone(true).removeClass('firstelement').appendTo('#accordion').find('button.fullshortcode').attr('id', sCounter).closest('.tab-content');
        parent_newly.find('.wp-picker-container').remove();
        parent_newly.find('.insert-picker').append('<input type="text" class="colorpicker" value="#333" />');

        parent_newly.find('.colorpicker').wpColorPicker();
        jQuery( "#accordion" ).accordion('refresh');

    });

    jQuery('#accordion .btndelete').click(function(event) {
         if (jQuery(this).closest('.ui-accordion-content').hasClass('firstelement')) {
            alert('You can not delete it as it is first element!');
        } else {
            var head = jQuery(this).closest('.ui-accordion-content').prev();
            var body = jQuery(this).closest('.ui-accordion-content');
            head.remove();
            body.remove();
            jQuery("#accordion").accordion('refresh');
        }

    });

    jQuery('button.fullshortcode').click(function(event) {
        event.preventDefault();
        prompt("Copy and use this Shortcode", '[compact-post-previewer id="'+jQuery(this).attr('id')+'"]');
    });
});