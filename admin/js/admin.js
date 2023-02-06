if (typeof sendit === "undefined"){
    async function sendit(location, senddata){
        const settings = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:senddata
        };
        try {
            const fetchResponse = await fetch(location, settings);
            const receivedata = await fetchResponse.json();
            return receivedata;
        } catch (e) {
            console.log(e);
            return e;
        } 
        
    }
    window.sendit = sendit;
}

function add_calendar(event){
    event.preventDefault();
    console.log("adding calendar");
    let location = rbtgc.ajaxurl + '?action=rbtgc_add_calendar';
    let nonce = rbtgc.nonce;
    let calnum = event.target.dataset.calNumber;
    let input_name = 'calendar_' + calnum;
    let cal_input = document.querySelector('input[name="' + input_name + '"]');
    let parent = event.target.closest('.parent');
    let response = parent ? parent.querySelector('.response') : false;
    if(response) response.innerHTML = '';
    if(cal_input && nonce){
        let sendbody = '&calendar=' + encodeURIComponent(cal_input.value) + '&nonce=' + encodeURIComponent(nonce);
        sendit(location, sendbody).then( (r) => {           
            if(r.status === 200){         
                if(response){
                    response.className = '';
                    response.classList.add('success')
                    response.innerHTML = r.payload;
                }
            } else {
                if(response) {
                    response.className = '';
                    response.classList.add('error')
                    response.innerHTML = r.payload;
                }
            }
        })

    } else {
        if(response) response.innerHTML = 'Form Error: Please log in again';
    }
}
function remove_calendar(event){
    event.preventDefault();
    let location = rbtgc.ajaxurl + '?action=rbtgc_remove_calendar';
    let nonce = rbtgc.nonce;
    let calendar = event.target.dataset.calendar;
    if(calendar && nonce){
        let sendbody = '&calendar=' + encodeURIComponent(calendar) + '&nonce=' + encodeURIComponent(nonce);
        sendit(location, sendbody).then( (r) => {
            let parent = event.target.closest('.parent');
            if(r.status === 200){
                if(response){
                    response.className = '';
                    response.classList.add('success')
                    response.innerHTML = r.payload;
                }
            } else {
                if(response) {
                    response.className = '';
                    response.classList.add('error')
                    response.innerHTML = r.payload;
                }
            }
        })

    } else {
        if(response) response.innerHTML = 'Form Error: Please log in again';
    }
}
function set_timezone(event){
    event.preventDefault();
    let location = rbtgc.ajaxurl + '?action=rbtgc_set_timezone';
    let nonce = rbtgc.nonce;
    let timezone = event.target.value;
    let parent = event.target.closest('.parent');
    let response = parent ? parent.querySelector('.response') : false;
    if(response) response.innerHTML = '';
    if(timezone && nonce){
        let sendbody = '&timezone=' + encodeURIComponent(timezone) + '&nonce=' + encodeURIComponent(nonce);
        sendit(location, sendbody).then( (r) => {
            if(r.status === 200){
                
                if(response){
                    response.className = '';
                    response.classList.add('success')
                    response.innerHTML = r.payload;
                }
            } else {
                if(response) {
                    response.className = '';
                    response.classList.add('error')
                    response.innerHTML = r.payload;
                }
            }
        })
    } else {
        if(response) response.innerHTML = 'Form Error: Please log in again';
    }
}
