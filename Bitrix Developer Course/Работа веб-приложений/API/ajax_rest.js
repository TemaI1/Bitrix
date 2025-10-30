$(document).ready(function () {
  $("form").submit(function (event) {
    var formData = {
      query: $("#ip").val(),
    };
	var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address?ip=";
	var token = "bc031aca4c4dab46950d2dc18d0e25dc342e8cba";

    $.ajax({
      type: "GET",
      url: url + formData.query,
	  beforeSend: function(xhr) {
                 xhr.setRequestHeader("Authorization", "Token "+ token) 
            },
      data: '',
      dataType: "json",
      encode: true,
    }).done(function (result) {
      console.log(result);

        // Записываем значение
        document.getElementById("country").textContent = "Страна: " + result.location.data.country;
        document.getElementById("city").textContent = "Город: " + result.location.value;
	});

    event.preventDefault();
  });
});