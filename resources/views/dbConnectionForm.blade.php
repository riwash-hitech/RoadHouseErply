<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Database connection check</title>
</head>

<body class="container">
    <h1>Check DB Connection </h1>
    <form id="form-check-db">
        @csrf
        <div class="mb-3">
            <label class="form-label">Host</label>
            <input type="text" class="form-control" required name="host">
        </div>
        <div class="mb-3">
            <label class="form-label">UserName</label>
            <input type="text" class="form-control" required name="username">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" required name="password">
        </div>
        <div class="mb-3">
            <label class="form-label">DB Name</label>
            <input type="text" class="form-control" required name="dbName">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <div class="mt-1" id="loading" style="display:none">
        <i class="fa fa-spinner fa-spin"></i>
        Loading...
    </div>
    <div class="mt-2" id="message"></div>
    <div class="mt-4" id="product-table"></div>

</html>
<script>
    $(document).ready(function() {
        $("#form-check-db").submit(function(e) {
            e.preventDefault();
            $("#loading").show();
            $.ajax({
                type: "POST",
                url: "{{ route('dbConnectionCheck') }}",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        $("#loading").hide();
                        var table =
                            "<h2>Stock Item Detail</h2>" +
                            "<table class='table table-striped'>" +
                            "<thead>" +
                            "<tr>" +
                            "<th>Stock Code</th>" +
                            "<th>Stock Name</th>" +
                            "</tr>" +
                            "</thead>" +
                            "<tbody>";
                        for (var i = 0; i < response.data.length; i++) {
                            table += "<tr>" +
                                "<td>" + response.data[i].STOCKCODE + "</td>" +
                                "<td>" + response.data[i].Description + "</td>" +
                                "</tr>";
                        }
                        table += "</tbody>" +
                            "</table>";
                        $("#message").text(response.message);
                        $("#product-table").show();
                        $("#product-table").html(table);
                    } else {
                        $("#message").text(response.message);
                        $("#loading").hide();
                        $("#product-table").hide();
                    }
                },
                error: function(xhr, status, error) {
                    $("#loading").hide();
                    $("#message").text(xhr.responseText);
                }
            });
        });
    });
</script>