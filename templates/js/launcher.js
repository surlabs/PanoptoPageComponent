let launched = false;

function addForm(data, url, host){

    if(!launched){
        $("#ilContentContainer").append(`<iframe name="basicltiLaunchFrame"  id="basicltiLaunchFrame" src="${host}/Panopto/BasicLTI/BasicLTILanding.aspx" style="display:none;"></iframe>`);

        launched = true;
        let form = document.createElement('form');
        form.id = 'lti_form';
        form.method = 'post';
        form.action = url;
        form.target = 'basicltiLaunchFrame';
        form.enctype = 'application/x-www-form-urlencoded';

        for (const key in data) {
            if (Object.hasOwnProperty.call(data, key)) {
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
}
function addVideo(id, host, isPlaylist){
    setTimeout(()=>{
        $(".ppco_iframe_container_"+id).append(`
    <iframe src='https://${host}/Panopto/Pages/Embed.aspx?${isPlaylist ? 'p' : ''}&id=${id}&v=1' frameborder='0' allowfullscreen style='width:100%;aspect-ratio: 16/9'></iframe>`)
    }, 500);
}