function weedesignCheckForWebPSupport(feature, callback) {
  var kTestImages = {
      lossy: "UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA",
      lossless: "UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==",
      alpha: "UklGRkoAAABXRUJQVlA4WAoAAAAQAAAAAAAAAAAAQUxQSAwAAAARBxAR/Q9ERP8DAABWUDggGAAAABQBAJ0BKgEAAQAAAP4AAA3AAP7mtQAAAA=="
  };
  var img = new Image();
  img.onload = function () {
    var result = (img.width > 0) && (img.height > 0);
    callback(feature, result);
  };
  img.onerror = function () {
    callback(feature, false);
  };
  img.src = "data:image/webp;base64," + kTestImages[feature];
}

function weedesignWebPFallback() {
  var divs = document.getElementsByClassName('weedesign-webp');
    if(divs.length>0) {
      for(var i=0; i<divs.length; i++) {
        if(typeof(divs[i].dataset.fallback)!=typeof(this_is_not_defined)) {
          divs[i].src = divs[i].dataset.fallback;
          divs[i].removeAttribute("srcset");
        }
      }
    }
}

document.addEventListener("DOMContentLoaded", function(event) {

  var features = ["lossy","lossless","alpha"];
  for(var i=0; i<features.length; i++) {
    weedesignCheckForWebPSupport(features[i], function (feature, isSupported) {
      if (!isSupported) {
        weedesignWebPFallback();
      }
    });
  }

});