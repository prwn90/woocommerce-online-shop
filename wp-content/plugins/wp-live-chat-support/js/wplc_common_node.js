/* Regex for inline links */
var tcx_link_match_regex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!,.;:<>]*[-A-Z0-9+&@#\/%=~_|<>])/ig;

/**
 * Setup supported file suffix types
 */
var tcx_file_suffix_check = [
  "zip", "pdf", "txt", "mp3", "mpa", "ogg", "wav", "wma", "7z", "rar",
  "db", "xml", "csv", "sql", "apk", "exe", "jar", "otf", "ttf", "fon",
  "fnt", "ai", "psd", "tif", "tiff", "ps", "msi", "doc", "docx",
];

var wplc_baseurl = config.baseurl;
var WPLC_SOCKET_URI = config.serverurl;

function wplc_safe_html(s) {
  return jQuery("<div>").text(s).html().replace("'", '&apos;').replace('"', '&quot;');
}

function wplc_uploaded_file_decorator(url) {
  // this is an uploaded file
  var nmsg = '';
  url = wplc_sanitize_url(url);
  if (url) {
    var ext = url.split(/\#|\?/)[0].split('.').pop().trim();
    if (ext.match(/jpg|jpeg|gif|bmp|png/)) {
      // it's an image, show preview
      nmsg = '<p><a href="' + url + '" target="_blank"><img src="' + url + '" style="max-width:64px;max-height:64px" alt="image"/></a></p>';
    }
    if (nmsg == '') {
      // default url renderer
      nmsg = wp_url_decorator(url);
    }
  }
  return nmsg;
}

function wplcFormatParser(msg) {
  msg = wplc_safe_html(msg);

  // assume uploaded files messages are one liners, discard everything else is not included in link
  var tags = ['img', 'link', 'video', 'vid']; // handle all tags with same routine
  for (var i = 0; i < tags.length; i++) {
    var url = msg.match(new RegExp('^' + tags[i] + ':(.*?):' + tags[i] + '$'));
    if (url && url[1]) {
      return wplc_uploaded_file_decorator(url[1]);
    }
  }

  // if there's a link, don't decorate further - risk of breaking it
  if (msg.match(tcx_link_match_regex)) {
    return wp_url_decorator(msg);
  }

  // Fix double emojis
  if (msg.search(/\:(\S+)(\:)(\S+)\:/g) !== -1) {
    msg = msg.replace(/\:(\S+)(\:)(\S+)\:/g, function(match, p1, p2, p3) {
      return [":", p1, "::", p3, ":"].join('');
    });
  }

  // emoticons renderer
  if (typeof wdtEmojiBundle !== "undefined") {
    msg = wdtEmojiBundle.render(msg);
  }

  var italics_match = msg.match(/_([^*]*?)_/g);
  if (italics_match !== null) {
    for (var i = 0, len = italics_match.length; i < len; i++) {
      var to_find = italics_match[i];
      var to_replace = to_find.substring(1, to_find.length - 1); // remove the starting _ and ending _
      msg = msg.replace(to_find, "<em>" + to_replace + "</em>");
    }
  }

  var bold_match = msg.match(/\*\s*([^*]*?)\s*\*/g);
  if (bold_match !== null) {
    for (var i = 0, len = bold_match.length; i < len; i++) {
      var to_find = bold_match[i];
      var to_replace = to_find.substring(1, to_find.length - 1); // remove the starting * and ending *
      msg = msg.replace(to_find, "<strong>" + to_replace + "</strong>");
    }
  }

  var pre_match = msg.match(/```([^*]*?)```/g);
  if (pre_match !== null) {
    for (var i = 0, len = pre_match.length; i < len; i++) {
      var to_find = pre_match[i];
      var to_replace = to_find.substring(3, to_find.length - 3); // remove the starting ``` and ending ```
      msg = msg.replace(to_find, "<pre>" + to_replace + "</pre>");
    }
  }

  var code_match = msg.match(/`([^*]*?)`/g);
  if (code_match !== null) {
    for (var i = 0, len = code_match.length; i < len; i++) {
      var to_find = code_match[i];
      var to_replace = to_find.substring(1, to_find.length - 1); // remove the starting ` and ending `
      msg = msg.replace(to_find, "<code>" + to_replace + "</code>");
    }
  }

  msg = msg.replace(/\n/g, "<br />");
  return msg;
}

function wp_url_decorator(content) {
  return content.replace(tcx_link_match_regex, function(url) {
    url = encodeURI(url);
    return '<a href="' + url + '" target="_BLANK">' + wp_attachment_label_filter(url) + '</a>';
  });
}

/**
 * Check if string contains any file suffixes
 * If so return 'Attachment' - Else Return self
 *
 * @param {string} content Content to filter
 * @return {string} Fitlered content
 */
function wp_attachment_label_filter(content) {
  var fileExt = content.split('.').pop();
  var fileName = wplc_safe_html(content.split('/').pop());
  fileExt = fileExt.toLowerCase();
  for (var i in tcx_file_suffix_check) {
    if (fileExt === tcx_file_suffix_check[i]) {
      return '<p class="wplc_uploaded_file"><i class="fa fa-file"></i><span>' + fileName + '</span></p>';
    }
  }
  return content;
}

function wplc_sanitize_url(url) {
  return url.replace(/[^-A-Za-z0-9+&@#/%?=~_|!:,.;\(\)]/, '');
}


/**
 * Removes undesired strings from a message which contains a GIF URL and returns only the gif url
 * @param {*} message_content 
 */
function wplc_get_clean_gifurl(message_content) {
  var url = message_content.match(gifExtensionPattern);
  if (url && url[0]) {
    return wplc_sanitize_url(url[0]);
  }
  return "";
}

/**
 * Perform chat auto popup
 * @param {*} mode (0,1,2) 
 */
function wplc_auto_popup_do(mode) {
  switch (mode) {
    case 1:
      setTimeout(function() { if (!jQuery('#wp-live-chat-header').hasClass('active')) {jQuery('#wp-live-chat-header').click();} }, 1000);
      break;
    case 2:
      setTimeout(function() { open_chat(0); }, 1000);
      break;
  }
  // Adding this fixes the bug that stops the chat window from opening when an agent initialises a chat with a user
  // Reasoning: when running open_chat(), wplc_is_chat_open is set to TRUE at the end of the function, and stops any future request to open_chat();
  wplc_is_chat_open = false;
}