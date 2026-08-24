$(document).ready(function(){
    $(".opt_delete_account a").click(function(){
        $("#dialog-delete-account").dialog('open');
    });

    $("#dialog-delete-account").dialog({
        autoOpen: false,
        modal: true,
        buttons: [
            {
                text: sigma.langs.delete,
                click: function() {
                    window.location = sigma.base_url + '?page=user&action=delete&id=' + sigma.user.id  + '&secret=' + sigma.user.secret;
                }
            },
            {
                text: sigma.langs.cancel,
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
});