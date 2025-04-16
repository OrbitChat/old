<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orbit - Encrypted Rooms</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 200px;
      height: 100%;
      color: white;
      padding-top: 20px;
    }
    #sidebar a {
      color: white;
      text-decoration: none;
      padding: 10px;
      display: block;
    }
    #sidebar a:hover {
      background-color: #495057;
    }
    .content {
      margin-left: 170px;
    }
  </style>
</head>
<body class="text-light" style="background-color:rgb(16, 15, 15);">
  <div id="sidebar" class="" style="width: 250px;">
    <img src="/img/orbit.png" class="ml-5" style="width: 400px; height: 80px;" />
  </div>
  <div class="content">
    <div class="container mt-4">
      <div class="card text-light" style="background-color: #121212; height: 670px;">
      <div class="card-body">
      <div style="width: 1055px; height: 570px; overflow-y: auto;" id="output"></div>
        <div id="msgControl" style="width: 1050px; display: flex; margin-top: 20px; margin-right: 250px; align-items: center; gap: 10px;">
          <input type="text" id="msg" class="form-control form-control-lg" style="margin-left: 270px; height: 50px; color: white; background-color: rgb(11, 11, 11); border: 2px solid rgb(30, 30, 30); width: 370px;" placeholder="Message" />
          <button onclick="send()" class="btn btn-primary" style="border: 2px solid rgb(30, 30, 30); background-color:rgb(11, 11, 11); height: 50px; width: 100px;"><i class="fas fa-paper-plane" style="color: #ffffff;"></i></button>
        </div>
      </div>
      </div>
      </div>
  </div>
  </div>
  </div>

  <script>
    const socket = new WebSocket('ws://<?= $_SERVER['HTTP_HOST']; ?>:8080/socket');
    const params = new URLSearchParams(window.location.search);
    var establishMsg = false;
    var decryption = '';
    var username = '';
    var authenticated = false;
    socket.binaryType = 'arraybuffer';

    socket.onopen = function(event) {
        console.log('[Orbit] Prompted user for authentication')
        decryption = prompt('Enter decryption passphrase: ');
        socket.send(JSON.stringify({action: 'a2', id: params.get('id'), enc: decryption}));
    }

    socket.onmessage = function(event) {
      const uint8ArrayData = new Uint8Array(event.data);
      
      try {
        const decompressedData = pako.ungzip(uint8ArrayData, { to: 'string' });
        
        if (!establishMsg) {
            establishMsg = true;
            console.log('[Orbit] Established socket connection, version: ' + decompressedData);
        } else { 
            data = JSON.parse(decompressedData);
           
            if (data.action == 'r2') {
                if (!data.valid) {
                    console.log('[Orbit] User failed authentication')
                    window.location.reload();
                } else {
                    console.log('[Orbit] User passed authentication');
                    console.log('[Orbit] Prompting user for a username');
                    username = prompt('Choose a username: ');
                    console.log('[Orbit] Chosen username is ' + username);
                }
            } else if (data.action == 'r3') {
              if (data.room == params.get('id')) {
                document.getElementById('output').insertAdjacentHTML('beforeend', '<p style="display: inline; font-weight: bold; font-size: 25px;">' + CryptoJS.AES.decrypt(data.username, decryption).toString(CryptoJS.enc.Utf8) + ': </p><p style="display: inline; font-size: 23px; color: rgb(133, 133, 133);">' + CryptoJS.AES.decrypt(data.message, decryption).toString(CryptoJS.enc.Utf8) + '</p><br>');
                document.getElementById('output').scrollTop = document.getElementById('output').scrollHeight;
              }
            }
        }
      } catch (err) {
        console.error('[Orbit] Unable to decompress');
      }
    };

    function send() {
      socket.send(JSON.stringify({action: 'a3', room: params.get('id'), enc: decryption , message: CryptoJS.AES.encrypt(document.getElementById('msg').value, decryption).toString(), username: CryptoJS.AES.encrypt(username, decryption).toString()}));
      document.getElementById('msg').value = '';
    }

    document.getElementById.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      send();
    }
  });
  </script>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>
</body>
</html>
