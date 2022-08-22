jQuery(document).ready(function(){
  jQuery(".togglebutton button").click(function(){
    jQuery("#nav-primary ul.menu").slideToggle();
    jQuery(this).toggleClass('btn-open').toggleClass('btn-close');
  });
  var copyData = jQuery('.google-fullcalendar .fc-center').html(); 
  jQuery(".fc-button-group .fc-prev-button" ).after( copyData );
  jQuery('.portfolio-wrapper>ul>li>a[href=""]').css("display", "none");
  jQuery('.banner_slider .slider').slick({
    centerMode: true,
    centerPadding: '0',
    autoplay: true,
    slidesToShow: 1,
    autoplaySpeed: 4000,
  });
  jQuery('.gallery-home .slider').slick({
    centerMode: true,
    centerPadding: '0px',
    slidesToShow: 4,
    slidesToScroll: 1,
    asNavFor: jQuery('.slider'),
    dots: false,
    arrows: false,
    focusOnSelect: true,
    responsive: [
      {
        breakpoint: 768,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '0px',
          slidesToShow: 3
        }
      },
      {
        breakpoint: 480,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '0px',
          slidesToShow: 1
        }
      }
    ]
  });
  jQuery(".search_section").on("click", function(){
    jQuery('.searchoverlay').css('top',0);
  });
  jQuery('.closebtn').on('click', function(){
    jQuery('.searchoverlay').css('top',-230);
  });
  jQuery('.gal-image>img').on('change', function(){
	  jQuery('.slick-arrow:submit').trigger('click');	
	}); 
  jQuery('.soc-slider .slider').slick({
    centerMode: true,
    centerPadding: '0',
    autoplay: true,
    slidesToShow: 5,
    autoplaySpeed: 4000,
    index: 2,
    responsive: [
      {
        breakpoint: 768,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '0px',
          slidesToShow: 3
        }
      },
      {
        breakpoint: 480,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '0px',
          slidesToShow: 1
        }
      }
    ]
  });
  jQuery(function () {    
    var filterList = {
      init: function () {
        jQuery('#portfoliolist').mixItUp({
          selectors: {
            target: '.portfolio',
            filter: '.filter' 
          },
          load: {
            filter: '.isotope-filter1'  
          }     
        });                      
      }
    };
    filterList.init();  
  });
 jQuery(".increase").click(function(){
    var currentfontsize = jQuery('p').css('font-size');
    var incfontsize = parseFloat(currentfontsize, 16);
    var newsize = incfontsize*1.2;
    jQuery('p').css('font-size', newsize);
    return false;
  });
 jQuery(".decrease").click(function(){
    var currentfontsize = jQuery('p').css('font-size');
    var decfontsize = parseFloat(currentfontsize, 16);
    var newsize = decfontsize*0.8;
    jQuery('p').css('font-size', newsize);
    return false;
  });


}); 
jQuery(document).on("scroll", function(){
	if (jQuery(document).scrollTop() > 64){
		jQuery(".hd-bottom").addClass("shrink");
	} else {
		jQuery(".hd-bottom").removeClass("shrink");
	}
	if (window.matchMedia('(max-width: 767px)').matches) {
    jQuery(".hd-bottom").removeClass("shrink");
  } 
});
jQuery(window).on("load resize scroll",function(e){
  
  jQuery('.grid').isotope({
    itemSelector: '.grid-item',
    percentPosition: true,
    masonry: {
      columnWidth: '.grid-sizer',
      gutter: '.gutter-sizer'
    }
  })
  
});
