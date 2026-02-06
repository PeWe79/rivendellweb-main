var qrcode = new QRCode(document.getElementById("qrcode"), {
    text: HOST_URL+'/touch/panel',
    logo: HOST_URL+"/touch/assets/web-app-manifest-192x192.png",
    logoWidth: undefined,
    logoHeight: undefined,
    logoBackgroundColor: '#ffffff',
    logoBackgroundTransparent: false
});
