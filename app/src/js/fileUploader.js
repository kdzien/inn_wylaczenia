import {WaitingModal} from './waitingmodal.js';

export function fileLoader(){
    let currentProduct = 'Polmo';
    let sendFileButton = document.getElementById('sendFileButton')
    let autButton_polmo = document.getElementById('autButton_polmo')
    let autButton_heko = document.getElementById('autButton_heko')
    let autButton_turbiny = document.getElementById('autButton_turbiny')
    let runProcedureButton = document.getElementById('runProcedureButton')
    let removeOnes = document.getElementById('removeOnes')
    let updateOnes = document.getElementById('updateOnes')
    let waitingModal = new WaitingModal('waiting-modal','waiting-circle','waiting-window','closeModal','infoPara');
    $('a[data-toggle="tab"]').on('click', function (e) {
        currentProduct = ($(this).text()).trim();
    })
    sendFileButton.addEventListener('click',e=>{        
        var file_data = $('#fileToUpload').prop('files')[0];   
        var form_data = new FormData();    
        form_data.append('file',file_data);
        waitingModal.toggleModal();
        saveFile(form_data).then(success=>{
            console.log(success)
            waitingModal.showWindow("Wgrano plik");
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    autButton_polmo.addEventListener('click',e=>{
        waitingModal.toggleModal();
        autProcess().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    autButton_turbiny.addEventListener('click',e=>{
        waitingModal.toggleModal();
        runProcedure().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    autButton_heko.addEventListener('click',e=>{
        waitingModal.toggleModal();
        runProcedure().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    runProcedureButton.addEventListener('click',e=>{
        waitingModal.toggleModal();
        runProcedure().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    removeOnes.addEventListener('click',e=>{
        waitingModal.toggleModal();
        removeOns().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })
    updateOnes.addEventListener('click',e=>{
        waitingModal.toggleModal();
        updateOns().then(success=>{
            waitingModal.showWindow(success.body);
        }).catch(err=>{
            waitingModal.showWindow(err.responseText);
        })
    })

    function saveFile(file){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: `http://localhost/wylaczenia_new/api/index.php/addfile/${currentProduct}`, 
                dataType: 'text',  
                cache: false,
                contentType: false,
                processData: false,
                data: file,                         
                type: 'post',
                success: function(success){
                    resolve(success); 
                },
                error: function(error){
                    reject(error); 
                }
            });
        })
    }
    function autProcess(){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: 'http://localhost/wylaczenia_new/api/index.php/automatyzacja',                        
                type: 'get',
                success: function(success){
                    resolve(success); 
                },
                error: function(error){
                    reject(error); 
                }
            });
        })
    }
    function runProcedure(){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: `http://localhost/wylaczenia_new/api/index.php/procedura/${currentProduct}`,                        
                type: 'get',
                success: function(success){
                    resolve(success); 
                },
                error: function(error){
                    reject(error); 
                }
            });  
        })
    }
    function removeOns(){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: 'http://localhost/wylaczenia_new/api/index.php/removeOns',                        
                type: 'get',
                success: function(success){
                    resolve(success); 
                },
                error: function(error){
                    reject(error); 
                }
            });  
        })
    }
    function updateOns(){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: 'http://localhost/wylaczenia_new/api/index.php/updateOns',                        
                type: 'get',
                success: function(success){
                    resolve(success); 
                },
                error: function(error){
                    reject(error); 
                }
            }); 
        })
    }
}
    