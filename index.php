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
      <div class="card text-light" style="background-color: #121212">
        <div class="card-header">
          <h5 class="card-title text-center" style="font-size: 27px;">Statistics</h5>
        </div>
        <div class="card-body">
          <p id="status" class="text-danger" style="font-size: 20px;">Socket is down</p>
          <p id="version" class="text-warning" style="font-size: 20px;">Unable to retrieve socket version</p>
        </div>
      </div>
  </div>
      <div class="container mt-2" style="">
      <div class="card text-light" style="background-color: #121212; min-height: 430px;">
        <div class="card-header">
           <h5 class="card-title text-center" style="font-size: 27px;">Past Rooms</h5>
        </div>
        <div class="card-body">
          <p class="text-warning" style="font-size: 20px;">No rooms yet!</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    const socket = new WebSocket('ws://<?= $_SERVER['HTTP_HOST']; ?>:8080/socket');
    var establishMsg = false;
    socket.binaryType = 'arraybuffer';

    socket.onopen = function(event) {
        document.getElementById('status').innerHTML = "Connected to socket";
        document.getElementById('status').className = 'text-success';
    }

    socket.onmessage = function(event) {
      const uint8ArrayData = new Uint8Array(event.data);
      
      try {
        const decompressedData = pako.ungzip(uint8ArrayData, { to: 'string' });

        if (!establishMsg) {
            establishMsg = true;
            console.log('[Orbit] Established socket connection, version: ' + decompressedData);
            document.getElementById('version').innerHTML = "Version: " + decompressedData;
            document.getElementById('version').className = '';
        } 
      } catch (err) {
        console.error('[Orbit] Unable to decompress: ', err);
      }
    };
  </script>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
