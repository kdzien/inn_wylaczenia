export class WaitingModal{
    constructor(modal,circle,window,button,info){
        this.isHide = true;
        this.modal = $(`#${modal}`);
        this.closebutton = $(`#${button}`).click(()=>{
            this.toggleModal();
        });
        this.window = window;
        this.circle = circle;
        this.info = info;
    }
    toggleModal(){
        if(this.isHide){
            this.modal.css({'display':'block'})
            this.showCircle();
        }else{
            this.modal.css({'display':'none'})
        }
        this.isHide = !this.isHide;
    }
    showCircle(){
        $(`#${this.window}`).css({'display':'none'})
        $(`#${this.circle}`).css({'display':'block'})
    }
    showWindow(message){
        $(`#${this.info}`).text(message);
        $(`#${this.window}`).css({'display':'block'})
        $(`#${this.circle}`).css({'display':'none'})
    }
}