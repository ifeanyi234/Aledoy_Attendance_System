<?php
// Add-jobs page removed. Redirect to dashboard.
header('Location: dashboard.php');
exit;

?>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="dashboard.php">
                        <!-- Logo icon -->
                        <b class="logo-icon">
                            <!-- Dark Logo icon -->
                            <img src="../Images/images-removebg-preview.png" style="max-width:120px; height:auto;" alt="homepage" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="" alt="" />
                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none"
                        href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav d-none d-md-block d-lg-none">
                        <li class="nav-item">
                            <a class="nav-toggler nav-link waves-effect waves-light text-white"
                                href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav d-flex align-items-center">

                        <!-- ============================================================== -->
                        <!-- User profile -->
                        <!-- ============================================================== -->
                        <li>
                            <a class="profile-pic" href="#">
                                <img src="plugins/images/users/varun.jpg" alt="user-img" width="36"
                                    class="img-circle"></a>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Current Time -->
                        <!-- ============================================================== -->
                        <li class="ms-auto">
                            <span class="text-white font-medium" id="current-time"></span>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Time Display -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <?php include('side-nav.php'); ?>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="margin-top: 30px;">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->

            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">New Job Post</h4>
                    </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                        <div class="d-md-flex">
                            <ol class="breadcrumb ms-auto">
                                <li><a href="#" class="fw-normal"></a></li>
                            </ol>
                            <!-- <a href="proc-add-question.php" target=""
                                    class="btn btn-danger  d-none d-md-block pull-right ms-3 hidden-xs hidden-sm waves-effect waves-light text-white">Save
                                    </a> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.col-lg-12 -->

            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <!-- <div class="container-fluid">
                <div class="container">
                    <textarea name="message" placeholder="Enter Question">
                </div>
            </div> -->
            <div class="container-fluid">
                <?php if ($error) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>
                <?php if ($success) echo '<div class="alert alert-success">' . $success . '</div>'; ?>

                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <!-- <h3 class="box-title">Basic Table</h3> -->
                            <!-- <p class="text-muted">Add class <code>.table</code></p> -->
                            <div class="">
                                <form action="processJob" method="post" enctype="multipart/form-data">
                                    <div class="row">


                                        <div class="img-text col-md-6">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="<?php echo $title; ?>" >
                                            <br>
                                        </div>                                      

                                        <div class="img-text col-md-6">
                                            <label>State</label>
                                            <input type="text" name="state" class="form-control" value="<?php echo $state; ?>" >
                                            <br>
                                        </div>

                                        <div class="img-text col-md-6">
                                            <label>Country</label>
                                            <input type="text" name="country" class="form-control" value="<?php echo $country; ?>" >
                                            <br>
                                        </div>

                                        <div class="img-text col-md-6">
                                            <label>Location</label>
                                            <input type="text" name="location" class="form-control" value="<?php echo $location; ?>" >
                                            <br>
                                        </div>

                                        <div class="img-text col-md-6">
                                            <label>Job Type</label>
                                            <input type="text" name="job_type" class="form-control" value="<?php echo $job_type; ?>" >
                                            <br>
                                        </div>

                                        <div class="img-text col-md-6">
                                            <label>Salary</label>
                                            <input type="text" name="salary" class="form-control" value="<?php echo $salary; ?>" >
                                            <br>
                                        </div>

                                        <div class="img-text col-md-6">
                                            <label>Deadline</label>
                                            <input type="date" name="deadline" class="form-control" value="<?php echo $deadline; ?>" >
                                            <br>
                                        </div>   

                                        <div class="img-text col-md-6">
                                            <label>Job Url</label>
                                            <input type="text" name="job_url" class="form-control" value="<?php echo $job_url; ?>" >
                                            <br>
                                        </div>    
                                                                               
                                        <div class="img-text  col-md-6">
                                            <label>Qualification</label>
                                            <textarea name="qualification" rows="4" class="form-control" cols="50" placeholder="Enter your message here" ><?php echo $qualification; ?></textarea>
                                            <script>
                                                CKEDITOR.replace('qualification');
                                            </script>
                                            <br>

                                        </div>

                                        <div class="img-text  col-md-6">
                                            <label>Experience</label>
                                            <textarea name="experience" rows="4" class="form-control" cols="50" placeholder="Enter your message here" ><?php echo $experience; ?></textarea>
                                            <script>
                                                CKEDITOR.replace('experience');
                                            </script>
                                            <br>

                                        </div>

                                        <div class="img-text  col-md-6">
                                            <label>Description</label>
                                            <textarea name="description" rows="4" class="form-control" cols="50" placeholder="Enter your message here" ><?php echo $description; ?></textarea>
                                            <script>
                                                CKEDITOR.replace('description');
                                            </script>
                                            <br>

                                        </div>

                                        <div class="img-text  col-md-6">
                                            <label>Skills</label>
                                            <textarea name="skills" rows="4" class="form-control" cols="50" placeholder="Enter your message here" ><?php echo $skills; ?></textarea>
                                            <script>
                                                CKEDITOR.replace('skills');
                                            </script>
                                            <br>

                                        </div>

                                        <div class="img-text  col-md-12">
                                            <label>Responsibilities</label>
                                            <textarea name="responsibilities" rows="4" class="form-control" cols="50" placeholder="Enter your message here" ><?php echo $responsibilities; ?></textarea>
                                            <script>
                                                CKEDITOR.replace('responsibilities');
                                            </script>
                                            <br>

                                        </div>

                                        <!-- <div class="img-text">
                                            <label>Background Image</label>
                                            <input type="file" name="userfile" class="form-control" >
                                            <br>
                                        </div>

                                        <div class="img-text">
                                            <label>Date Published</label>
                                            <input type="date" name="datePublished" class="form-control" >
                                            <br>
                                        </div> -->


                                        <div class="img-text" style="margin-top: 40px;">
                                            <input class="btn btn-success" style=" margin-bottom: 10px;" type="submit" value="Add Job">
                                        </div><br>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right sidebar -->
        <!-- ============================================================== -->
        <!-- .right-sidebar -->
        <!-- ============================================================== -->
        <!-- End Right sidebar -->
        <!-- ============================================================== -->
    </div>




    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    <footer class="footer text-center"> Copyright © <?php echo date('Y') ?> - All Rights Reserved Aledoy Solution Limited </a>
    </footer>
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.js"></script>

</body>

</html>