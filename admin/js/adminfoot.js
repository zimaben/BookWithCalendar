console.log("adminfoot running");
addEventListener('DOMContentLoaded', () => {
    let addcalendar = document.getElementById('addcalendar');
    if(addcalendar) addcalendar.addEventListener('click', (event) =>{ add_calendar(event)});
    let settimezone = document.getElementById('settimezone');
    if(settimezone) settimezone.addEventListener('change', (event) =>{set_timezone(event)});
    let removecalendar = document.getElementsByClassName('removecalendar');
    if(removecalendar){
        for(let r of removecalendar) r.addEventListener('click', (event) =>{ remove_calendar(event)});
    }

})