/*
	VTwin JS Validation
	Requires validate.js, jQuery 1.8+ and the VTwin PHP class
*/

(function() {
	var self = this;

    var Vtwin;

    // Export gluten if exports are available
    if(typeof exports !== 'undefined') {
        Vtwin = exports;
    } else {
        Vtwin = self.Vtwin = {};
    }


    var formName;
    var formId;
    var submitUrl;
    var customRender;
    var customSuccess = false;
    var customQuery   = {}
    var noServer      = {}

    var errors = Vtwin.errors = function(handler) {
    	self.customRender = handler;
    }

    var success = Vtwin.success = function(handler) {
    	self.customSuccess = handler;
    }

    var display = {
    	errors: function(errorList) {
	    	$.each(errorList, function(k, field) {
	    		self.customRender("#"+field.id, field.message);
	    	});
	    },

	    cleanup: function() {
	    	$(self.formId).find(".error").remove();
	    }
    }

    var init = Vtwin.init = function(formName, query, skipServer) {
    	var data       = {"load":1}
    	self.formName  = formName;
    	self.formId    = "#"+formName;
    	self.submitUrl = $(self.formId).attr("action");

    	if (query) {
    		self.customQuery = query;
    	}

    	if (skipServer) {
    		self.noServer = {"noserver":true};
    	}


		$.ajax({
			  url: self.submitUrl,
			  data: $.extend(data,self.customQuery),
			  dataType: "json",
			  success:  function(vObj) {
				attach(vObj["rules"], vObj["messages"]);
			}
		});
    }

    var attach = function(rules, messages) {

		new FormValidator(self.formName, rules, messages,
			function(errors, event, fields) {
				display.cleanup();

				event.preventDefault();

			    if (errors.length > 0) {
			        display.errors(errors);
			    } else {
			        frontcheck();
			    }
			}
		);
    }

    var frontcheck = function() {
    	var formdata = {"verify":1,"fd":$(self.formId).serializeArray()};

		$.ajax({
			  url: self.submitUrl,
			  data: $.extend(formdata,self.customQuery,self.noServer),
			  dataType: "json",
			  success:  function(serverErrors) {
			  	if (serverErrors.length > 0) {
					display.errors(serverErrors);
				} else {
					document.getElementById(self.formName).onsubmit = null; // Remove any bindings added by the validate plugin
					
					if (!self.customSuccess) {
						$.each(self.customQuery, function(k,v) {
			    			$(self.formId).append('<input type="hidden" name="'+k+'" value="'+v+'" />');
			    		});						
						$(self.formId).submit();
					} else {
						self.customSuccess();
					}
				}
			}
		});    	
    }


}).call(this);