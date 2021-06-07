window.addEventListener("message",
    function (data) {
        if(data.data.action == 'permissions_granted') {
            document.getElementById('permissions_content').innerHTML = "Loading...";
            window.location.href = base_url;
        }
    }
);
function fire_lazers() {
    $('.player_row').each(function(){
        trigger_attack($(this).attr('data-playerid'));
    });
}
function trigger_attack(player_id) {
    var use_all = 0;
    if(document.querySelector('input[name="attack_priority_level"]:checked').value == "all_resources") {
        use_all = 1;
    }
    var target_ip = $('#selected_target').val();
    if($('.player_row[data-playerid='+player_id+']').html().trim()=="None" || use_all) {
        $('.player_row[data-playerid='+player_id+']').html("Firing up lazers...");

        $.ajax({
          type: 'POST',
          url: '?act=trigger_attack',
          player_id: player_id,
          target_ip: target_ip,
          use_all: use_all,
          data: {
             "player_id": player_id,
             "ip": target_ip,
             "use_all": use_all
          },
          dataType: 'json',
          success: function (data) {
            if($('.player_row[data-playerid='+this.player_id+']').html().trim()=="None" || this.use_all) {
                $('.player_row[data-playerid='+this.player_id+']').html(this.target_ip);
            }
            console.log("success",data);
          },
          error: function(e, type, message) {
            console.log("ERROR", e, type, message);
          }
      });
    }
}
function trigger_perm_request(perm) {
    window.parent.postMessage({'action': 'request_permissions', 'permissions': [perm]}, '*');
}

function lock_on() {
    if(document.getElementById('ip').value.trim() == '') {
        document.getElementById('selected_target').value = "N O N E !";
        $('#lazer_button').removeClass('shake');
    }
    else {
        document.getElementById('selected_target').value = document.getElementById('ip').value;
        $('#lazer_button').addClass('shake');
    }
}
function charge_lazer() {
    $('#lazer_button').removeClass('shake');

    $('#selected_target').css('border-radius', '50%');
    $('#selected_target').addClass('explode');

    setTimeout(function(){
        $('#selected_target').addClass('unexplode');
    }, 2000);

    setTimeout(function(){
        $('#selected_target').removeClass('explode').removeClass('unexplode');
        $('#selected_target').css('border-radius', '0%');
        $('#attack_status_cell').addClass('flash');
    }, 3000);

    setTimeout(function(){
        $('#attack_status_cell').removeClass('flash');
    }, 6000);
    
    fire_lazers();
}