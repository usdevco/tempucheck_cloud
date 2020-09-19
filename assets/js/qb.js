
        var url = $("#qu_authUrl").val();

        var OAuthCode = function(url) {

            this.loginPopup = function (parameter) {
                this.loginPopupUri(parameter);
            }

            this.loginPopupUri = function (parameter) {

                // Launch Popup
                var parameters = "location=1,width=800,height=650";
                parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                            location.reload();
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }
        }


    var oauth = new OAuthCode(url);

    $(document).on('click','.connect_to_qb_btn',function(e)
    {
        oauth.loginPopup();
    });

    $(document).on('click','.record_in_qb_btn',function(e)
    {
        var order_id = parseInt($(this).attr("data-id")); 
        if(order_id)
        {
             $.ajax({
                    url: admin_url+'invoices/save_qb_order_invoice/'+order_id,
                    type: "post",
                    data: {order_id : order_id,} ,
                    dataType: 'json',
                    success: function (response) 
                    {
                        // response = $.parseJSON(response);
                        
                        if(response.success)
                        {
                            console.log(response.message);
                            location.reload(); 
                        }
                        else
                        {
                            console.log(response.message);
                            location.reload();
                        }                     
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                    }
                });
        }
        else
        {
            alert('invalid Order Id');
        }

    });


$(document).on("click",".qb_exported",function(){
    alert('Invoice Is Already Exported To QuickBokk');
});        

$(document).on("click",".btn_qb_connected",function(){
    alert('QuickBook Is Already Connected');
});

$(document).on("click",".qb_not_exported",function(){
    alert('This invoice has not been exported to QuickBooks yet');
});

$(document).on("click",".qb_delete_exported",function(e)
{
    var qb_invoice_id =  parseInt($(this).attr("data-qb_invoice_id"));
    var invoice_id = parseInt($(this).attr("data-id"));     
    if(invoice_id && qb_invoice_id)
    {
        if (confirm('- Are you sure you want to delete your invoice?  ')) 
        {
            $.ajax({
                    url: admin_url+'invoices/remove_qb_order_invoice/'+qb_invoice_id+'/'+invoice_id,
                    type: "post",
                    data: {qb_invoice_id : qb_invoice_id,invoice_id : invoice_id,} ,
                    dataType: 'json',
                    success: function (response) 
                    {
                        // response = $.parseJSON(response);
                        
                        if(response.success)
                        {
                            console.log(response.message);
                            location.reload(); 
                        }
                        else
                        {
                            console.log(response.message);
                            location.reload();
                        }                     
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                    }
                });
           
        }
       
    }
    else
    {
        alert('Sorry Invalid Invoice Id Or Order Id ! ');
    }


});


