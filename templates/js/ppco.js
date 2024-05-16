let videoTag = "Video";
let maxWidthTag = "Maximale Breite in %";
$(document).ready(function () {
    let get_new_form_groups = function(id, index, embedString, isPlaylist) {
        let form_groups = `<div class="panel panel-primary panel-flex" bis_skin_checked="1">
            <div class="panel-heading ilHeader" bis_skin_checked="1">
            <h2>${videoTag}</h2> 
            
            </div>
            <div class="panel-body" bis_skin_checked="1">`;

        form_groups += '<input type="hidden" class="xpan_form_element" name="session_id[]" value="'+id+'">';
        form_groups += '<input type="hidden" class=xpan_form_element" name="is_playlist[]" value="' + (isPlaylist ? 1 : 0) + '">';

        form_groups += '<div class="form-group xpan_form_element" id="il_prop_cont_embed_'+index+'">';

        form_groups += '<div class="col-sm-12">';
        form_groups += '<div class="form_inline">';
        form_groups += embedString;
        form_groups += '</div>';
        form_groups += '</div>';
        form_groups += '</div>';

        form_groups += '<div class="form-group xpan_form_element" id="il_prop_cont_max_width_'+index+'"  style="margin-top:10px;">';
        form_groups += '<h2 class="col-sm-12 control-label">'+maxWidthTag+'<span class="asterisk">*</span></h2>';
        form_groups += '<div class="col-sm-12">';
        form_groups += '<div class="form_inline">';
        form_groups += '<input style="text-align:right;width:auto;" class="form-control" type="text" size="10" id="max_width_'+index+'" maxlength="200" name="max_width[]" required="required" value="50">';
        form_groups += '<div class="help-block"></div>';
        form_groups += '</div>';
        form_groups += '</div>';
        form_groups += '</div>';
        form_groups += '</div>';
        form_groups += '</div>';


        return form_groups;
    };

    let iframe = $('#xpan_iframe'),
        iframe_src = iframe.attr('src'),
        servername = iframe_src.substr(0, iframe_src.indexOf('/Panopto/Pages/Sessions/EmbeddedUpload.aspx')),
        insert_button = $('.btn-primary[aria-label="insert"]'),
        eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent',
        eventEnter = window[eventMethod],
        messageEvent = eventMethod === 'attachEvent' ? 'onmessage' : 'message',
        choose_videos_link = $('.il-standard-form-header');

    //Hide insert button initially, until a video is selected
    insert_button.prop('disabled', true);


    // Listen to message from child iframe
    eventEnter(messageEvent, function (e) {
        // console.log(e);

        //Comprobamos si e.data es JSON y si no lo se, lo parseamos:
        try {
            e.data = JSON.parse(e.data);
        }
        catch (e) {
            return;
        }


        let message = JSON.parse(e.data),
            thumbnailChunk = '',
            idChunk = '',
            embedString = '',
            ids = message.ids,
            names = message.names,
            VIDEO_EMBED_ID = 0,
            PLAYLIST_EMBED_ID = 1;

        //If a video is chosen, show the "Insert" button
        if (message.cmd === 'ready') {
            insert_button.prop('disabled', false);
        }

        //If no video is chosen, hide the "Insert" button
        if (message.cmd === 'notReady') {
            insert_button.prop('disabled', true);
        }

        //Called when "Insert" is clicked. Creates HTML for embedding each selected video into the editor
        if (message.cmd === 'deliveryList') {
            // remove existing form elements
            $('.panel.panel-primary.panel-flex').remove();
            ids = message.ids;
            for (var i = (ids.length - 1); i >= 0; --i) {
                let isPlaylist = false;
                if (message.playableObjectTypes && (parseInt(message.playableObjectTypes[i]) === PLAYLIST_EMBED_ID)){
                    idChunk = "?pid=" + ids[i];
                    isPlaylist = true;
                } else {
                    idChunk = "?id=" + ids[i];
                }

                embedString = "<iframe class='xpan_form_element' id='iframe_"+i+"' src='" + servername + "/Panopto/Pages/Embed.aspx" +
                    idChunk + "&v=1' width='450' height='256' frameborder='0' allowfullscreen></iframe>";

                // add new form elements (iframe + max_width)
                $(get_new_form_groups(ids[i], i, embedString, isPlaylist)).insertAfter(choose_videos_link);
            }
            $('#ilContentContainer .modal').modal('hide')
        }
    }, false);

    insert_button.click(function () {
        let win = document.getElementById('xpan_iframe').contentWindow,
            message = {
                cmd: 'createEmbeddedFrame'
            };
        win.postMessage(JSON.stringify(message), servername);
    });

});

function setLenguage(video, max_width) {
    videoTag = video;
    maxWidthTag = max_width;
}
