jQuery(document).ready(function ($) {
  if ($("#cai-tabs").length) {
    $("#cai-tabs").tabs();
  }
  if ($(".color-picker").length) {
    $(".color-picker").wpColorPicker();
  }

  $("#cai-settings-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    const feedback = $("#cai-settings-feedback");
    const button = form.find(".button-primary");
    const originalText = button.val();

    button.val("Saving...").prop("disabled", true);
    feedback.empty().removeClass("notice-success notice-error");

    const settingsData = {};
    form.serializeArray().forEach((item) => {
      settingsData[item.name] = item.value;
    });
    form.find('input[type="checkbox"]').each(function () {
      settingsData[this.name] = this.checked ? "1" : "0";
    });

    $.ajax({
      url: communityAiAdmin.ajax_url,
      type: "POST",
      data: {
        action: "community_ai_save_settings",
        nonce: communityAiAdmin.nonce,
        settings: settingsData,
      },
      success: function (res) {
        const noticeClass = res.success ? "notice-success" : "notice-error";
        feedback
          .addClass(`notice ${noticeClass} is-dismissible`)
          .html(`<p>${res.data.message}</p>`);
      },
      error: function () {
        feedback
          .addClass("notice notice-error is-dismissible")
          .html("<p>An unexpected error occurred.</p>");
      },
      complete: function () {
        button.val(originalText).prop("disabled", false);
        setTimeout(() => feedback.fadeOut().empty(), 4000);
      },
    });
  });

  $("#cai-generate-now").on("click", function (e) {
    e.preventDefault();
    const button = $(this);
    const feedback = $("#cai-generate-feedback");
    const originalText = button.text();

    button.text("Generating...").prop("disabled", true);
    feedback.text("").removeClass("success error");

    $.ajax({
      url: communityAiAdmin.ajax_url,
      type: "POST",
      data: {
        action: "community_ai_generate_now",
        nonce: communityAiAdmin.nonce,
      },
      success: function (res) {
        feedback
          .text(res.data.message)
          .addClass(res.success ? "success" : "error");
      },
      error: function () {
        feedback.text("Request failed.").addClass("error");
      },
      complete: function () {
        button.text(originalText).prop("disabled", false);
      },
    });
  });
});
