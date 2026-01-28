<!DOCTYPE html>
<html>

<head>
    <title>A21</title>
    <!-- add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- <link rel="stylesheet" type="text/css" href="index.css" /> -->

    <style>
        .container-fluid {

            width: 100%;
            margin-left: -10px;

        }

        .drawer-box {
            position: relative;
            background-color: #222;
            color: #fff;
            min-height: 680px;
            margin-top: 2px;
        }

        .drawer-header {
            padding: 20px;
            text-align: center;
        }


        .drawer-body {

            top: 100%;
            left: 0;
            width: 100%;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }

        .drawer-menu {
            list-style: none;

            margin: 0;
            padding: 10;
        }

        .drawer-menu li a {
            display: block;
            padding: 20px;
            color: #fff;
            font-size: 18px;
            font-family: 'Trebuchet MS', sans-serif;
            text-decoration: none;
            transition: color 0.3s;
        }

        .drawer-menu li a:hover {
            color: rgb(137, 83, 236);
        }
    </style>

<body>
    <header class="p-4 py-4" style="background-color: grey;">

    </header>

    <main class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <div class="drawer-box">
                    <div class="drawer-header">
                        <h6> Forcast AP21 Export</h6>
                    </div>
                    <!-- <div class="border"></div> -->
                    <div class="drawer-body">
                        <ul class="drawer-menu">

                            <li><a href="{{ route('get.supplier.export') }}" target="_blank">1. Supplier Export</a></li>
                            <li><a href="{{ route('get.customer.export') }}" target="_blank">2. Customer Export</a></li>
                            <li><a href="{{ route('get.product.export') }}" target="_blank">3. Product Export</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>




    <footer class="bg-light py-3">
        <div class="container">

        </div>
    </footer>

    <!-- add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>
