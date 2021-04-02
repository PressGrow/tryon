var tryon={};var currentFrame='';window.onload=function(){var tryonid=document.getElementById('tryonpreview');var tryonbarid=document.getElementById('tryonbar');showVto(tryonid,tryonbarid)};function showVto(tryonId,barId){tryon=new Tryon();tryon.initApp(tryonId,barId)}
function close_tryon(){document.getElementById('tryonBox').style.display='none';document.getElementById('black_overlay').style.display='none'}
function resetTryon(tryonId,barId){tryonId.innerHTML="";barId.innerHTML="";tryon=new Tryon();tryon.initApp(tryonId,barId)}
function showalert(event,elem){return!1}
function grabNotices(){EventListener.addEventListener("requestAllowCamera",allowCamNotice);EventListener.addEventListener("cameraPermissionDenied",allowCamNotice);EventListener.addEventListener("noCamFound",allowCamNotice)}
function allowCamNotice(){alert("Please allow camera, in order to grab a pic")}
function webcamready(){alert("webcam loaded")}
function applyFrame(image,framePos){currentFrame=image;document.getElementById('tryonBox').style.display='block';document.getElementById('black_overlay').style.display='block';EventListener.dispatch("APPLY_FRAME",this,image,framePos)}