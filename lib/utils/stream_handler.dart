class StreamHandler {
  static String? extractDirectLink(String url) {
    if (url.contains('drive.google.com')) {
      final id = _getGoogleDriveId(url);
      if (id != null) return 'https://docs.google.com/get_video_info?docid=$id';
    }
    if (url.contains('mega.nz')) {
      // Mega.nz usually requires a specific client or decryption
      // In a real app, we'd use a mega library or a webview
      return url;
    }
    return url;
  }

  static String? _getGoogleDriveId(String url) {
    final regExp = RegExp(r'[-\w]{25,}');
    return regExp.firstMatch(url)?.group(0);
  }
}
