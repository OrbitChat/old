<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orbit - Encrypted Rooms</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>
  <style>
    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 250px;
      height: 100%;
      background-color: #343a40;
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
      margin-left: 260px;
    }
  </style>
</head>
<body class="text-light" style="background-color:rgb(16, 15, 15);">
  <div id="sidebar" class="" style="background-color: #121212; width: 250px;">
    <img src="/img/orbit.png" class="ml-4" style="width: 400px; height: 80px;" />
    <br /><br />
    <a href="/" class="text-center" style="font-size: 20px; font-weight: 650;">Home</a>
    <a href="/createRoom" class="text-center" style="font-size: 20px; font-weight: 650;">Create Room</a>
    <a href="/createRoom" class="text-center" style="font-size: 20px; font-weight: 650;">Join Room</a>
  </div>
  <div class="content">
    <div class="container mt-5">
      <div class="card text-light" style="background-color: #121212; min-height: 200px;">
        <div class="card-header">
          <h5 class="card-title text-center" style="font-size: 27px;">Create Room</h5>
        </div>
        <div class="card-body">
        <div style="display: flex; align-items: center; gap: 10px;">
        <input type="password" id="passInput" class="form-control form-control-lg" style="color: white; background-color: rgb(11, 11, 11); border: 2px solid rgb(30, 30, 30); width: 500px;" placeholder="Encryption Passphrase" />
        <button onclick="createRoom()" class="btn btn-primary" style="border: 2px solid rgb(30, 30, 30); background-color:rgb(11, 11, 11); height: 50px; width: 270px;">Create</button>
      </div>
      </div>
      </div>
  </div>
  </div>
  <div class="content">
    <div class="container mt-5">
      <div class="card text-light" style="background-color: #121212; min-height: 200px;">
        <div class="card-header">
          <h5 class="card-title text-center" style="font-size: 27px;">Join Room</h5>
        </div>
        <div class="card-body">
        <div style="display: flex; align-items: center; gap: 10px;">
        <input type="password" id="roomId" class="form-control form-control-lg" style="color: white; background-color: rgb(11, 11, 11); border: 2px solid rgb(30, 30, 30); width: 500px;" placeholder="Room ID" />
        <button onclick="joinRoom()" class="btn btn-primary" style="border: 2px solid rgb(30, 30, 30); background-color:rgb(11, 11, 11); height: 50px; width: 270px;">Join</button>
      </div>
      </div>
      </div>
  </div>
  </div>

  <script>
    const socket = new WebSocket('ws://<?= $_SERVER['HTTP_HOST']; ?>:8080/socket');
    var establishMsg = false;
    socket.binaryType = 'arraybuffer';

    socket.onmessage = function(event) {
      const uint8ArrayData = new Uint8Array(event.data);
      
      try {
        const decompressedData = pako.ungzip(uint8ArrayData, { to: 'string' });

        if (!establishMsg) {
            establishMsg = true;
            console.log('[Orbit] Established socket connection, version: ' + decompressedData);
        } else { 
            data = JSON.parse(decompressedData);
            
            if (data.action == 'r1') {
                window.location = 'room.php?id=' + data.id;
            }
        }
      } catch (err) {
        console.error('[Orbit] Unable to decompress: ', err);
      }
    };

    function createRoom() {
        if (!establishMsg) {
            alert('Websocket is unreachable, refresh and try again');
        } else {
            socket.send(JSON.stringify({action: 'a1', enc: document.getElementById('passInput').value}));
        }
    }

    function joinRoom() {
      window.location = "/room?id=" + document.getElementById('roomId').value;
    }
  </script>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
