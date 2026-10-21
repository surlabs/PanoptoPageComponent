
const panoptoLauncher = {
    videos: [],
    launched: false,
    lti_submitted: false,
    // Safari and every browser on iOS block third-party cookies, so the hidden LTI launch cannot sign the user in
    blocksThirdPartyCookies: function() {
        let ua = navigator.userAgent,
            is_ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1),
            is_safari = /^((?!chrome|chromium|crios|fxios|edg|android).)*safari/i.test(ua);

        return is_ios || is_safari;
    },
    launchInNewTab: function(url) {
        let tab = window.open('', 'xpan_panopto');

        if (!this.lti_submitted) {
            this.lti_submitted = true;
            let form = document.getElementById('lti_form');
            form.target = tab ? 'xpan_panopto' : '_blank';
            form.submit();

            if (tab) {
                setTimeout(() => {
                    tab.location.href = url;
                }, 3000);
            }
        } else if (tab) {
            tab.location.href = url;
        }
    },
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

            if (!this.blocksThirdPartyCookies()) {
                this.lti_submitted = true;
                form.submit();
            }
        }
    },
    addVideo : function (id, host, isPlaylist, randomId, openLabel) {
        setTimeout(()=>{
            $("#ppco_iframe_container_"+randomId).append(`
        <iframe src='https://${host}/Panopto/Pages/Embed.aspx?${isPlaylist ? 'p' : ''}id=${id}&v=1' frameborder='0' allowfullscreen style='width:100%;aspect-ratio: 16/9'></iframe>`)   ;
            if (this.blocksThirdPartyCookies()) {
                let viewerUrl = `https://${host}/Panopto/Pages/Viewer.aspx?${isPlaylist ? 'p' : ''}id=${id}`;
                $('<button type="button" class="btn btn-default"></button>')
                    .text(openLabel)
                    .on('click', () => this.launchInNewTab(viewerUrl))
                    .appendTo("#ppco_iframe_container_"+randomId);
            }
            if(!this.videos.some(video => video[1] === randomId)){
                this.videos.push([$("#ppco_iframe_container_"+randomId).html(), randomId]);
            }
        }, 500);

    },
};