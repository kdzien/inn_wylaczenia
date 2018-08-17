import {WaitingModal} from './waitingmodal.js';

export function fileLoader(){
    let currentProduct = 'Polmo';
    let sendFileButton = document.getElementById('sendFileButton')
    let autButton_polmo = document.getElementById('autButton_polmo')
    let autButton_heko = document.getElementById('autButton_heko')
    let autButton_turbiny = document.getElementById('autButton_turbiny');
    let checkPolmoTurns = document.getElementById("checkPolmoTurns");
    let checkRezawTurns = document.getElementById("checkRezawTurns")
    let clearKPLPOLMO = document.getElementById('clearKPLPOLMO')
    let clearKPLREZAW = document.getElementById('clearKPLREZAW');
    let autButton_rezaw = document.getElementById('autButton_rezaw');
    let removePolmo = document.getElementById('removePolmo')
    let updatePolmo = document.getElementById('updatePolmo')
    let sendJobs = document.getElementById('sendJobs');
    let removeRezaw = document.getElementById('removeRezaw')
    let updateRezaw = document.getElementById('updateRezaw')
    let waitingModal = new WaitingModal('waiting-modal','waiting-circle','waiting-window','closeModal','infoPara');
    $('a[data-toggle="tab"]').on('click', function (e) {
        currentProduct = ($(this).text()).trim();
        $('#fileToUpload').val('');
    })
    function succesStep(elem){
        $(elem).parent().parent().css({"background-color":"#b4ffb4"})
    }
    sendFileButton.addEventListener('click',function(e){     
        var file_data = $('#fileToUpload').prop('files')[0];   
        var form_data = new FormData();    
        form_data.append('file',file_data);
        waitingModal.toggleModal();
        saveFile(form_data).then(success=>{
            console.log(success)
            $( "#current-product" ).remove( "p" );
            $("#current-product").html(`<p>OBECNIE WGRANY PLIK: <span>${currentProduct}</span></p>`)
            waitingModal.showWindow("Wgrano plik");
        }).catch(err=>{
            console.log(err)
            waitingModal.showWindow(JSON.parse(err.responseText).error);
        })
    })

    let _procedures = [
        {button:autButton_polmo,procedure:"app_wylaczenia.automatyzacja_polmo()"},
        {button:autButton_turbiny,procedure:"app_wylaczenia.automatyzacja_turbiny()"},
        {button:autButton_heko,procedure:"app_wylaczenia.automatyzacja_heko()"},
        {button:autButton_rezaw,procedure:"app_wylaczenia.automatyzacja_rezaw()"},
        {button:clearKPLREZAW,procedure:"app_wylaczenia.clear_kpl_rezaw_wyl()"},
        {button:clearKPLPOLMO,procedure:"app_wylaczenia.clear_kpl_polmo_wyl()"},
        {button:checkPolmoTurns,procedure:"app_wylaczenia.spr_poprawnosci_wylaczen()"},
        {button:checkRezawTurns,procedure:"app_wylaczenia.spr_wl_wyl_rezaw()"}
    ]
    _procedures.forEach(elem=>{
        elem.button.addEventListener('click',function(e){
            let that = this;
            waitingModal.toggleModal();
            runProcedure(elem.procedure).then(success=>{
                waitingModal.showWindow(success.body);
                succesStep(that)
            }).catch(err=>{
                waitingModal.showWindow(err.responseText);
            })
        })
    })
    let _removes = [
        {button:removePolmo,removeq:"delete from ee_all.wylaczone_sku where sku in ( SELECT sku FROM app_wylaczenia.wl_wyl_polmo where do_wlaczenia=1 and data=current_date())"},
        {button:removeRezaw,removeq:"delete from ee_all.wylaczone_sku where sku in ( SELECT sku FROM app_wylaczenia.wl_wyl_rezaw where do_wlaczenia=1 and data=current_date())"}
    ]
    _removes.forEach(elem=>{
        elem.button.addEventListener('click',function(e){
            let that = this;
            waitingModal.toggleModal();
            removeOns(elem.removeq).then(success=>{
                waitingModal.showWindow(success.body);
                succesStep(that)
            }).catch(err=>{
                waitingModal.showWindow(err.responseText);
            })
        })
    })
    let _updates = [
        {button:updatePolmo,updateq:"update ee_all.wylaczone_aukcje_polmo set data_wlaczenia=current_timestamp() where sku in (SELECT sku FROM app_wylaczenia.wl_wyl_polmo where do_wlaczenia=1 and data=current_date()) and data_wlaczenia is null"},
        {button:updateRezaw,updateq:`UPDATE ee_all.wylaczone_aukcje_rezaw a
        INNER JOIN app_wylaczenia.wl_wyl_rezaw b
        on a.sku=b.sku
        and 
        case when b.country='GB' then a.country=b.country
        else a.country!='GB'
        end
        set a.data_wlaczenia=current_timestamp()
        where b.do_wlaczenia=1 
        and b.data=current_date()
        and a.data_wlaczenia is null`}
    ]
    _updates.forEach(elem=>{
        elem.button.addEventListener('click',function(e){
            
        console.log(elem)
            let that = this;
            waitingModal.toggleModal();
            updateOns(elem.updateq).then(success=>{
                waitingModal.showWindow(success.body);
                succesStep(that)
            }).catch(err=>{
                waitingModal.showWindow(err.responseText);
            })
        })
    })

    sendJobs.addEventListener('click',function(e){
        let that = this;
        waitingModal.toggleModal();
        $.ajax({
            url: `http://localhost/wylaczenia_new/api/index.php/gettemplates/${currentProduct}`, 
            type: 'get',
            success: function(success){
                $.ajax({
                    url : 'http://192.168.1.60/turbodziobak_new/api/jobs/ebay/create',
                    type : "POST",
                    'Authorization': `Basic YW5uYWs6YW5uYTIjZ3M=`,
                    crossDomain: true,
                    dataType: 'jsonp',
                    data: {
                        data_to_proceed : success.body.sql_template,
                        xml_template : success.body.xml_template,
                        description : success.body.description,
                        jobname : "ReviseInventoryStatus"
                    },
                    success : function(success) {
                        waitingModal.showWindow(success);
                    },
                    error: function(error){
                        if(error.status==200){
                            waitingModal.showWindow("sprawdz dziobaka");
                        }else{
                            console.log(error)
                            waitingModal.showWindow(error.responseText);
                        }
                        
                    }
                });
            },
            error: function(error){
                waitingModal.showWindow(error.responseText); 
            }
        });
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
    function runProcedure(sqlquery){
        return new Promise((resolve,reject)=>{
            console.log(currentProduct)
            $.ajax({
                url: `http://localhost/wylaczenia_new/api/index.php/procedura/${currentProduct}/${sqlquery}`,                        
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
    function removeOns(sqlq){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: `http://localhost/wylaczenia_new/api/index.php/removeOns/${sqlq}`,                        
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
    function updateOns(sqlq){
        return new Promise((resolve,reject)=>{
            $.ajax({
                url: `http://localhost/wylaczenia_new/api/index.php/updateOns/${sqlq}`,                        
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
    