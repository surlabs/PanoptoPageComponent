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

let videos = [];

function addVideo(id, host, isPlaylist){
    setTimeout(()=>{
        $(".ppco_iframe_container_"+id).append(`
    <iframe src='https://${host}/Panopto/Pages/Embed.aspx?${isPlaylist ? 'p' : ''}id=${id}&v=1' frameborder='0' allowfullscreen style='width:100%;aspect-ratio: 16/9'></iframe>`)
    }, 500);

    videos.push([id, host, isPlaylist]);
}


const originalFetch = fetch;
async function handleFetch(args) {
    const requestBodyJson = JSON.parse(args[1].body);
    const action = requestBodyJson.action;
    if (action === 'delete') {
        videos.forEach(video => {
            addVideo(video[0], video[1], video[2]);
        });
    }
}

window.fetch = async function(...args) {
    const response = await originalFetch.apply(this, args);
    // Llama a handleFetch y pasa los argumentos
    handleFetch(args).catch(error => console.error('Error en handleFetch:', error));
    return response;
};