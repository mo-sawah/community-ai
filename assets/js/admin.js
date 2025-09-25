jQuery(document).ready(function ($) {
  // Initialize Tabs for settings page
  if ($("#tabs").length) {
    $("#tabs").tabs();
  }

  // Initialize Color Picker
  if ($(".color-picker").length) {
    $(".color-picker").wpColorPicker();
  }

  // Handle Settings Form Submission
  $("#community-ai-settings-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    const feedbackDiv = $("#settings-save-feedback");
    const submitButton = form.find(".button-primary");
    const originalButtonText = submitButton.val();

    submitButton.val("Saving...").prop("disabled", true);
    feedbackDiv.empty().removeClass("notice-success notice-error");

    const formData = form.serializeArray();
    const settingsData = {};

    $.each(formData, function (i, field) {
      // Handle checkboxes that are not checked
      if (form.find(`input[type="checkbox"][name="${field.name}"]`).length) {
        // This logic needs to be more robust, for now we will assume it is sent
      }
      settingsData[field.name] = field.value;
    });

    // Handle unchecked checkboxes
    form.find('input[type="checkbox"]').each(function () {
      if (!this.checked) {
        settingsData[this.name] = "0";
      }
    });

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "community_ai_save_settings",
        nonce: settingsData.nonce,
        settings: settingsData,
      },
      success: function (response) {
        if (response.success) {
          feedbackDiv
            .addClass("notice notice-success is-dismissible")
            .html("<p>" + response.data.message + "</p>");
        } else {
          feedbackDiv
            .addClass("notice notice-error is-dismissible")
            .html(
              "<p>" + (response.data.message || "An error occurred.") + "</p>"
            );
        }
      },
      error: function () {
        feedbackDiv
          .addClass("notice notice-error is-dismissible")
          .html("<p>An unexpected error occurred.</p>");
      },
      complete: function () {
        submitButton.val(originalButtonText).prop("disabled", false);
        setTimeout(() => {
          feedbackDiv.fadeOut().empty();
        }, 4000);
      },
    });
  });
});
