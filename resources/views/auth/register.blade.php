<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/base.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

  <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/vendors.css">
  <link rel="stylesheet" href="css/main.css">

  <title>Educrat</title>
</head>

<body class="preloader-visible" data-barba="wrapper">
  <!-- preloader start -->
  <div class="preloader js-preloader">
    <div class="preloader__bg"></div>
  </div>
  <!-- preloader end -->


  <main class="main-content  
  bg-beige-1
">


    <div class="content-wrapper  js-content-wrapper">

      <section class="form-page">
        <div class="form-page__img bg-dark-1">
          <div class="form-page-composition">
            <div class="-bg"><img data-move="30" class="js-mouse-move" src="img/login/bg.png" alt="bg"></div>
            <div class="-el-1"><img data-move="20" class="js-mouse-move" src="img/home-9/hero/bg.png" alt="image"></div>
            <div class="-el-2"><img data-move="40" class="js-mouse-move" src="img/home-9/hero/1.png" alt="icon"></div>
            <div class="-el-3"><img data-move="40" class="js-mouse-move" src="img/home-9/hero/2.png" alt="icon"></div>
            <div class="-el-4"><img data-move="40" class="js-mouse-move" src="img/home-9/hero/3.png" alt="icon"></div>
          </div>
        </div>

        <div class="form-page__content lg:py-50">
          <div class="container">
            <div class="row justify-center items-center">
              <div class="col-xl-8 col-lg-9">
                <div class="px-50 py-50 md:px-25 md:py-25 bg-white shadow-1 rounded-16">
                  <h3 class="text-30 lh-13">Sign Up</h3>
                  <p class="mt-10">Already have an account? <a href="login.html" class="text-purple-1">Log in</a></p>

                  <form class="contact-form respondForm__form row y-gap-20 pt-30" action="#">
                    <div class="col-lg-6">
                      <label class="text-16 lh-1 fw-500 text-dark-1 mb-10">Email address *</label>
                      <input type="text" name="title" placeholder="Name">
                    </div>
                    <div class="col-lg-6">
                      <label class="text-16 lh-1 fw-500 text-dark-1 mb-10">Username *</label>
                      <input type="text" name="title" placeholder="Name">
                    </div>
                    <div class="col-lg-6">
                      <label class="text-16 lh-1 fw-500 text-dark-1 mb-10">Password *</label>
                      <input type="text" name="title" placeholder="Name">
                    </div>
                    <div class="col-lg-6">
                      <label class="text-16 lh-1 fw-500 text-dark-1 mb-10">Confirm Password *</label>
                      <input type="text" name="title" placeholder="Name">
                    </div>
                    <div class="col-12">
                      <button type="submit" name="submit" id="submit" class="button -md -green-1 text-dark-1 fw-500 w-1/1">
                        Register
                      </button>
                    </div>
                  </form>

                  <div class="lh-12 text-dark-1 fw-500 text-center mt-20">Or sign in using</div>

                  <div class="d-flex x-gap-20 items-center justify-between pt-20">
                    <div><button class="button -sm px-24 py-20 -outline-blue-3 text-blue-3 text-14">Log In via Facebook</button></div>
                    <div><button class="button -sm px-24 py-20 -outline-red-3 text-red-3 text-14">Log In via Google+</button></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


    </div>
  </main>

  <!-- JavaScript -->
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
  <script src="js/vendors.js"></script>
  <script src="js/main.js"></script>
</body>

</html>