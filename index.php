<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Stock Trading</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
      <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
   </head>
   <body>
        <form enctype="multipart/form-data" method="post" action="#" id="stockform">
        <div class="p-3 text-white bg-primary text-center">
            <h4>Stock Trading</h4>
        </div>
        <div class="container mt-5">
            <div class="row">
                <div class="col-sm-6">
                <div class="form-group">
                    <label for="customFile">Select File</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="csv" name="csv" required>
                        <label class="custom-file-label" for="csv">Choose file</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="drpStockName">Select Stock Name</label>
                    <select class="form-control" id="drpStockName" name="drpStockName" required>
                        <option value="">Select Stock Name</option>
                    </select>
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fromDate">From Date</label>
                                <input type="date" class="form-control" id="fromDate" name="fromDate" required> 
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="toDate">To Date</label>
                                <input type="date" class="form-control" id="toDate" name="toDate" required> 
                            </div>
                        </div>
                </div>
                <div class="text-right mt-4">
                        <button type="button" class="btn btn-secondary" id="btnClear">Clear</button>
                        <button type="submit" class="btn btn-primary">Get Stock Trading</button>
                </div>
                </div>
                <div class="col-sm-6">
                    <h6>Result</h6>
                    <div class="alert">
                        
                    </div>
                </div>
            </div>
        </div>
      </form>
      <script>
        // Add the following code if you want the name of the file appear on select
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });

        $("#btnClear").click(function() {
            location.reload();
        })

        $("#csv").change(function() {
            var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;
            const stockArray = [];
            if (regex.test($("#csv").val().toLowerCase())) {
                if (typeof (FileReader) != "undefined") {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var rows = e.target.result.split("\n");
                        console.log(typeof rows);   
                        for (var i = 1; i < rows.length; i++) {
                            var cells = rows[i].split(",");
                            if (cells.length > 1) {
                                stockArray.push(cells[2]);
                            }
                        }
                        
                        var uniqueStockname = stockArray.filter(function(item, i, stockArray) {
                            return i == stockArray.indexOf(item);
                        });

                        uniqueStockname.map(myFunction);
                    
                        console.log(uniqueStockname);
                    }
                    reader.readAsText($("#csv")[0].files[0]);
                } else {
                    alert("This browser does not support HTML5.");
                }
                $(".btn-primary").show();
            } else {
                alert("Please upload a valid CSV file.");
                $(".btn-primary").hide();
            }

            function myFunction(num) {
                $("#drpStockName").append("<option value="+num+">"+num+"</option>");
            }
        })

        // Bind to the submit event of our form
        $("#stockform").submit(function(e){
            e.preventDefault();
            var form = $("#stockform")[0];
            var formData = new FormData(form);
            
            console.log(formData);
            $.ajax({
                type: "POST",
                url: 'restIndex.php',
                data: formData,
                dataType: 'json',
                cache: false,
                processData:false,
                contentType: false,
                success: function(response)
                {
                    if(response.result == "success") {
                        var stock_result = response.data;
                        var purchased_date = '<ul>';
                        var sold_date = '<ul>';
                        for(var i = 0; i < stock_result.length; i++) {
                            if((stock_result[i]['price']) == (stock_result[0]['price'])) {
                                purchased_date = purchased_date + '<li>'+stock_result[i]['date'] +'</li>';
                            }
                            if(stock_result[i]['price'] == stock_result[stock_result.length-1]['price'] &&  stock_result.length != 1) {
                                sold_date = sold_date + '<li>'+stock_result[i]['date'] +'</li>';
                            }
                        }
                         purchased_date = purchased_date + '</ul>';
                         sold_date  = sold_date + '</ul>';
                         
                        var result = `
                                <small>To make more profit, you can purchased/sold on below mentioned date:</small>
                                <br><br>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        Purchased Date: <span id='purchased_date'>`+purchased_date+`</span>
                                    </div>
                                    <div class='col-md-6'>
                                        Sold Date: <span id='sold_date'>`+sold_date+`</span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-12'>
                                        Mean: `+ response.mean.toFixed(2) +` <br>
                                        Standard Deviation: `+ response.standardDeviation.toFixed(2) +`
                                    </div>
                                </div>
                                `;
                        $(".alert").prop('class','alert alert-success').html(result);
                    } else {
                        $(".alert").prop('class', 'alert alert-danger').html(response.result);
                    }
                }
            });

        });
      </script>
   </body>
</html>