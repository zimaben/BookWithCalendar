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
    let linkcalendar = document.getElementById('linkcalendar');
    if(linkcalendar) linkcalendar.addEventListener('click', (event) =>{ link_calendar(event)});
    /* TEMP @ToDo Delete when done testing */
    let dotest = document.getElementById('testingfeatures');
    if(dotest) dotest.addEventListener('click', (event) => { test_features(event)});
    

})