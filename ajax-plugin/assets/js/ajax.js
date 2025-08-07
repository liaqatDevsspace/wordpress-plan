jQuery(document).ready(function ($) {
  $.ajax({
    type: "POST",
    url: myData.url,
    data: {
      action: "get_books", // This is what triggers your PHP handler
      nonce: myData.nonce,
    },
    dataType: "json", // Expect JSON response
    success: function (response) {
      //add books to the page
      console.log(response.data);
      const $ul = $("<ul></ul>");
      response.data.forEach((item) => {
        const $li = $(`<li></li>`);
        $li.append(`<h2>${item.title}</h2>`);
        $li.append(`<p>${item.excerpt}</p>`);
        $li.append(`<a href="${item.link}">View Details</a>`);
        $ul.append($li);
      });
      $("#books").append($ul);
    },
    error: function (xhr, status, error) {
      console.error("AJAX error:", error);
    },
  });
});
