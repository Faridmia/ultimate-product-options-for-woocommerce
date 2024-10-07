(function ($) { 

  "use strict";
  $('.upow-flash-sale-item').each(function() {

      var date = $(this).data('date');
      var activeId = $(this).data('id');

      if($(this).length){

          var countDownDate = new Date(date).getTime();

          // Update the count down every 1 second
          var x = setInterval(function() {
          
            // Get today's date and time
            var now = new Date().getTime(); 
            // Find the distance between now and the count down date
            var distance = countDownDate - now;
              
            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
              
            // Output the result in an element with id="demo"
            document.getElementById(activeId).innerHTML = "<div class='upow-flash-sale-timer-item'><span class='upow-flash-sale-timer-item-number'>"+days + "</span><span class='upow-flash-sale-timer-item-title'>days</span></div><div class='upow-flash-sale-timer-item'><span class='upow-flash-sale-timer-item-number'>" + hours + "</span><span class='upow-flash-sale-timer-item-title'>hours </span></div><div class='upow-flash-sale-timer-item'><span class='upow-flash-sale-timer-item-number'>"
            + minutes + "</span><span class='upow-flash-sale-timer-item-title'>minutes</span></div><div class='upow-flash-sale-timer-item'><span class='upow-flash-sale-timer-item-number'>" + seconds + "</span><span class='upow-flash-sale-timer-item-title'>seconds</span></div>";
              
            // If the count down is over, write some text 
            if (distance < 0) {
              clearInterval(x);
              document.getElementById(activeId).innerHTML = "EXPIRED";
            }
          }, 1000);
      }

  });

  if($('.upow-flash-sale-banner-text').length > 0) {
    var cdtdate = $(".upow-flash-sale-countdown").data('cdtdate');
    var austDay = new Date(cdtdate);
    $('.upow-flash-sale-countdown').countdown({ until: austDay, layout: '<div class="flash-sale-item"><p>{dn}</p> {dl}</div> <div class="flash-sale-item"><p>{hn}</p> {hl}</div> <div class="flash-sale-item"><p>{mn}</p> {ml}</div> <div class="flash-sale-item"><p>{sn}</p> {sl}</div>' });
    $('#year').text(austDay.getFullYear());

    // smooth scrolling	
    $(function () {
      $('a[href*=#]:not([href=#])').click(function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {

          var target = $(this.hash);
          target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
          if (target.length) {
            $('html,body').animate({
              scrollTop: target.offset().top
            }, 1000);
            return false;
          }
        }
      });
    });
  }

})(window.jQuery);