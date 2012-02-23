/**
 * Administrations JS File
 * All functions are capseled into an own function/prototype.
 *
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.2
 * @copyright 2011-2012 Jacomeit.com
 */
/**
 * Changelog
 *
 * 0.2
 * - Creating the methode menu_walker for checking wich url are open
 *
 * 0.1
 * - Initial creating the file with trigger_nav_menu & document.open
 */
(function($){
    /**
     * Save the Request-Query-URI into global var
     */
    var query = location.pathname;

    /**
     * Walk the nav-menu and checks, if the user are on a subpage, to shows the
     * menu open
     * @since 0.2
     */
    menu_walker = {
        init: function(){
            $('.navigation > li').each(function(index){
                console.log($(this));
                var uri = $(this).children('ul').children('li').children('a').attr('href');
                var tmp = uri ? uri.split('/') : undefined;
                var q = query.split('/');
                if (tmp && (tmp[1] == q[1]) && !$(this).hasClass('open')) {
                    console.log($(this).children('ul'));
                    $(this).addClass('open');
                    $(this).css({"backgroundImage":"url(/img/bg_nav_open.gif"});
                    $(this).children('ul').show();
                }
                console.log('Query: '+q+' / TMP: '+tmp);
            });
        }
    };

	/**
	 * Triggers the clapped navigation-menu
	 * @since 0.1
	 */
	trigger_nav_menu = {
		init: function(){
			$('#sidebar>ul>li').css({cursor:'pointer'});
			if ($('#sidebar>ul>li').hasClass('open'))
				$('#sidebar>ul>li.open').css({"backgroundImage":"url(/img/bg_nav_open.gif)"}).children('ul').show();
			$('#sidebar>ul>li').on('click',function(){
				if($(this).has('ul').length > 0 && $(this).children('ul').is(':visible')){
					$(this).css("backgroundImage","url(/img/bg_nav_arrow.gif)");
					$(this).children('ul').fadeOut('fast');
				} else if ($(this).has('ul').length > 0 && $(this).children('ul').is(':hidden')) {
					$(this).css("backgroundImage","url(/img/bg_nav_open.gif)");
					$(this).children('ul').fadeIn('fast');
				}
			});
		}
	};

    /**
     * Checks the value of domainname into form-field if exists
     */
    check = {
        domain: function() {
            if (!$('input#domainname').length) return;
            $('input#domainname').on('change',function(){
                $.getJSON('/ajax.php',{action:'check','for':'domainname','domain':$(this).val()},function(res){
                    if (res.success) {
                        $('<img src="/img/accept.png" alt="Ok, existiert noch nicht" />').insertAfter(this);
                        if ($('input#save').attr('disabled')) $('input#save').removeAttr('disabled');
                    } else {
                        $('<img src="/img/error.png" alt="Domain existiert bereits." />').insertAfter(this);
                        $('input#save').attr('disabled','disabled');
                    }
                });
            });
        }
    };

    /**
     * Triggers the Click-Image Actions
     * @since 0.1
     */
    trigger_click_image = {
        init: function() {
            $('.pClick').css({cursor: 'pointer'});
            $('.pClick').on('click', function(){
                var id = $(this).attr('id');
                var tmp = id.split('|');
                switch(tmp[0]) {
                    case 'add':
                        action.add(tmp[1]);
                }
            });
        }
    };

	// Starts, if document are loaded
	$(document).ready(function($){
        trigger_click_image.init();
		trigger_nav_menu.init();
        check.domain();
        //menu_walker.init();
        $('.dataTable').dataTable({
            'oLanguage': {
                "sProcessing":   "Bitte warten...",
                "sLengthMenu":   "_MENU_ Einträge anzeigen",
                "sZeroRecords":  "Keine Einträge vorhanden.",
                "sInfo":         "_START_ bis _END_ von _TOTAL_ Einträgen",
                "sInfoEmpty":    "0 bis 0 von 0 Einträgen",
                "sInfoFiltered": "(gefiltert von _MAX_  Einträgen)",
                "sInfoPostFix":  "",
                "sSearch":       "Suchen",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Erster",
                    "sPrevious": "Zurück",
                    "sNext":     "Nächster",
                    "sLast":     "Letzter"
                }
            }
        });
	});

    /**
     * Prototyp for all actions
     */
    action = {
        add: function(what) {
            $('<div class="modal"></div>').appendTo('body');
            $('.modal').dialog({
                modal: true,
                width: 420
            });
            switch(what) {
                case 'server':
                    $('.modal').load('/tables/server.php?action=new&nohead=1').dialog({
                        buttons: {
                            'Speichern': function() {
                                $.getJSON('/ajax.php',{action:'save', 'do':'server', name: $('input#name').val()},function(res){
                                    if (res.success) {
                                        var select = $('.pClick').siblings('select#server');
                                        $('<option value="' + res.id + '" selected="selected">' + $('input#name').val() + '</option>').appendTo(select);
                                        $('.modal').dialog('close');
                                    } else {
                                        alert(res.error);
                                    }
                                });
                            },
                            'Abbrechen': function() { $(this).dialog('close'); }
                        }
                    }).dialog('open');
                    break;
                case 'network':
                    $('.modal').load('/tables/networks.php?action=new&nohead=1').dialog({
                        buttons: {
                            'Speichern': function() {
                                $.getJSON('/ajax.php',{action:'save', 'do':'network', name: $('input#networkname').val()},function(res){
                                    if (res.success) {
                                        var select = $('.pClick').siblings('select#netz');
                                        var option = '<option value="' + res.id + '" selected="selected">';
                                        option += res.subnetz ? "\t" : '';
                                        option += $('input#networkname').val() + '</option>'
                                        $(option).appendTo(select);
                                        $('.modal').dialog('close');
                                    } else {
                                        alert(res.error);
                                    }
                                });
                            },
                            'Abbrechen': function() { $(this).dialog('close'); }
                        }
                    }).dialog('open');
                    break;
            }
        }
    };
})(jQuery, jQuery);
