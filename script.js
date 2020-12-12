
//Prevent, "Do you want to resend data"
//message when reloading after form
//submission.
if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
}

//The bell schedule.
//[hrs,mins]
//Add 12 for P.M.
//E.G. 1 P.M. -> 13
var times = [
    [8,00],
    [9,00],
    [9,30],
    [10,00],
    [10,30],
    [11,30]
];

var alarmRan = false
//Check the time every second.
setInterval(function(){
    var now = new Date();
    //Check if the current time is
    //one where the bell should ring.
    for (i = 0; i < times.length; i++){
        //Check current time against one of the bell times.
        //Check that the bell hasn't rung/isn't ringing.
        if (now.getHours() === times[i][0] && now.getMinutes() === times[i][1] && !alarmRan)
        {
            //The bell should ring. Find which
            //class the user is in.
            var course = displayRadioValue(i);
            //Go to that class.
            changeClass(course);
        }
    }
//Do this every second. Increase for performance,
//decrease for precision.
}, 1000);

//Open a class.
function changeClass(course){
  //Log which class is being opened.
  console.log(course);

  //Ask the server for the link to that class.
  postAjax('/getLink.php', 'getLinkFromClassName=true&class='+course, function(data){

      //Log the link sent back from the server.
      console.log(data)

      //Open the link in a new tab.
      window.open(
          data, "_blank");

      //Play a bell sound.
      document.getElementById("bellsound").volume=0.5;
      document.getElementById("bellsound").play();

      //Stop the bell sound.
      setTimeout(function(){
          document.getElementById("bellsound").pause();
      },100);

  });

  //Prevent the alarm from
  //going off again for the
  //next minute.
  alarmRan = true;
  setTimeout(function (){
      alarmRan = false;
  }, 60000);

}

//Try to fade out the bell sound.
//DOES NOT WORK!

/*function getSoundAndFadeAudio (audiosnippetId) {

    var sound = document.getElementById(audiosnippetId);

    // Set the point in playback that fadeout begins. This is for a 2 second fade out.
    var fadePoint = sound.duration - 8.5;

    var fadeAudio = setInterval(function () {

        // Only fade if past the fade out point or not at zero already
        if ((sound.currentTime >= fadePoint) && (sound.volume != 0.0)) {
            sound.volume -= 0.1;
        }
        // When volume at zero stop all the intervalling
        if (sound.volume === 0.0) {
            clearInterval(fadeAudio);
        }
    }, 30);

}*/

//No idea how this works.
function displayRadioValue(name) {
    var ele = document.getElementsByName(name);

    for(i = 0; i < ele.length; i++) {
        if(ele[i].checked){
            return ele[i].value

        }
    }
}

//Send a POST request.
function postAjax(url, data, success) {
    var params = typeof data == 'string' ? data : Object.keys(data).map(
    function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
            ).join('&');

        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
        xhr.open('POST', url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState>3 && xhr.status==200) { success(xhr.responseText); }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(params);
        return xhr;
}

//Matomo
var _paq = window._paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
_paq.push(["setDomains", ["*.classes.jforseth.tech","*.192.168.1.5","*.localhost","*.classes.jforseth.tech"]]);
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
  var u="//matomo.jforseth.tech/";
  _paq.push(['setTrackerUrl', u+'matomo.php']);
  _paq.push(['setSiteId', '3']);
  var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
  g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
})();
//End Matomo Code


//Save radio buttons
//https://stackoverflow.com/questions/7198408/remember-radio-button-selections
//Answer doesn't work, but fiddle seems to.
$(function()
{
   $('input[type=radio]').each(function()
   {
       var state = JSON.parse( localStorage.getItem('radio_'  + this.id) );

       if (state) this.checked = state.checked;
   });
});

$(window).bind('unload', function()
{
   $('input[type=radio]').each(function()
   {
       localStorage.setItem(
           'radio_' + this.id, JSON.stringify({checked: this.checked})
       );
   });
});
