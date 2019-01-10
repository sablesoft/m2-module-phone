let config = {
    map: {
        '*': {
            sendCode:       'SableSoft_Phone/js/sendCode',
            'jquery.mask':  'SableSoft_Phone/js/jquery.mask'
        }
    },
    paths:{
        'jquery.mask' : "SableSoft_Phone/js/jquery.mask"
    },
    shim:{
        'jquery.mask' : {
            'deps':['jquery']
        }
    }
};
