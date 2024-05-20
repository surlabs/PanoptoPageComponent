
const panoptoLauncher = {
    videos: [],
    launched: false,
    addForm: function(data, url, host) {
        if (!this.launched) {
            $("#ilContentContainer").append(`<iframe name="basicltiLaunchFrame" id="basicltiLaunchFrame" src="${host}/Panopto/BasicLTI/BasicLTILanding.aspx" style="display:none;"></iframe>`);

            this.launched = true;
            let form = document.createElement('form');
            form.id = 'lti_form';
            form.method = 'post';
            form.action = url;
            form.target = 'basicltiLaunchFrame';
            form.enctype = 'application/x-www-form-urlencoded';

            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    const value = data[key];
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
            }

            document.getElementById('ilContentContainer').appendChild(form);

            form.submit();
        }
    },
    addVideo : function (id, host, isPlaylist, randomId) {
        setTimeout(()=>{
            const randomId2 = Math.floor(Math.random() * 1000000);
            $("#ppco_iframe_container_"+randomId).append(`
        <iframe src='https://${host}/Panopto/Pages/Embed.aspx?${isPlaylist ? 'p' : ''}id=${id}&v=1' frameborder='0' allowfullscreen style='width:100%;aspect-ratio: 16/9'></iframe>`)   ;
            if(!this.videos.some(video => video[1] === randomId)){
                this.videos.push([$("#ppco_iframe_container_"+randomId).html(), randomId]);
            }
        }, 500);

    },
};