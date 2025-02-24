<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา</title>
  </head>
  <body>
  <div class="container">
    <div class="row " style="background-color: #7109c0;">
      <div class="col col-sm-3">
        <img src="img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
      </div>

      <div class="col col-sm-8 d-flex justify-content-center align-items-center">
        <h2 class="text-center" align="center" style="color: white;">ระบบบันทึกเวลาปฏิบัติงานบุคลากรโรงเรียนวัดธรรมนาวา
        </h2>
      </div>
    </div>
  </div>
    <div class="container">
      <div class="row">
        <div class="col-sm-4"></div>
        <div class="col col-sm-4">
          <form action="authen.php"  method="post">
            <div class="form-group">
              <label for="m_username">รหัสบุคลากร</label>
              <input type="text" class="form-control" id="m_username" name="m_username"   placeholder="รหัสพนักงาน" minlength="2"  required>
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">รหัสผ่าน</label>
              <input type="password" class="form-control" id="exampleInputPassword1" name="m_password" placeholder="รหัสผ่าน" minlength="2"  required>
            </div>
            <button type="submit" class="btn btn-primary ">LOGIN</button>
          </form>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col col-sm-12">
          
          </div>
        </div>
      </div>
      <!-- Optional JavaScript -->
      <!-- jQuery first, then Popper.js, then Bootstrap JS -->
      <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </body>
  </html>