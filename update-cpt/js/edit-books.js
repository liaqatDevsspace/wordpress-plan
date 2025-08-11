jQuery(document).ready(function ($) {
  $(".update-book").on("click", function () {
    const parent = $(this).closest(".book-item");
    const bookID = parent.data("id");
    const newTitle = parent.find(".book-title").val();
    const status = parent.find(".status-message");
    $.ajax({
      url: ajax_object.ajax_url,
      method: "POST",
      data: {
        action: "update_book_title",
        security: ajax_object.nonce,
        book_id: bookID,
        new_title: newTitle,
      },
      beforeSend: function () {
        status.text("Saving...");
      },
      success: function (response) {
        console.log("helll");
        if (response.success) {
          status.text("Updated ✔");
          console.log("successful!");
          console.log(response);
        } else {
          console.log("error");
          status.text("Error: " + response.data.message);
        }
      },
      error: function (e) {
        status.text("Request failed.");
        console.log(e);
      },
    });
  });
});
