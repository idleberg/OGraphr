		jQuery(document).ready(function() {
		
				
		
				if (! jQuery("#show_advanced").attr('checked') ) {
					jQuery('.advanced_opt').hide();
				}
				jQuery("#show_advanced").click(function(){
					jQuery(".advanced_opt").fadeToggle('slow');
				});
					
				jQuery("#enable_plugin").click(enable_cb);
				jQuery("#enable_images").click(enable_images);
				jQuery("#enable_delete").click(enable_delete);

				jQuery("#enable_expiry input").click( function() {
				    var val = parseInt( this.value );
				    if ( val === 1 ) {
				        jQuery("select.no_expiry").removeAttr('disabled');
				    } else {
				        jQuery("select.no_expiry").attr( 'disabled', 'disabled' );
				    }
				});
				
				//render stats last!
				render_stats();		
		});

		function enable_cb() {
			if (this.checked) {
				jQuery("input.enable_triggers").removeAttr("disabled");
			} else {
				jQuery("input.enable_triggers").attr("disabled", true);
			}
		}
	
		function enable_images() {
			if (this.checked) {
				jQuery("input.disable_filters, textarea.disable_filters").removeAttr("disabled");
			} else {
				jQuery("input.disable_filters, textarea.disable_filters").attr("disabled", true);
			}
		}
	
		function enable_delete() {
			if (this.checked) {
				jQuery("input.enable_delete").removeAttr("disabled");
			} else {
				jQuery("input.enable_delete").attr("disabled", true);
			}
		}