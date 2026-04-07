import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';

class EmbedPlayerScreen extends StatelessWidget {
  final String url;
  const EmbedPlayerScreen({super.key, required this.url});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(title: const Text('Streaming Embed')),
      body: InAppWebView(
        initialUrlRequest: URLRequest(url: WebUri(url)),
        initialSettings: InAppWebViewSettings(
          javaScriptEnabled: true,
          mediaPlaybackRequiresUserGesture: false,
          allowsInlineMediaPlayback: true,
          contentBlockers: [
            ContentBlocker(
              trigger: ContentBlockerTrigger(urlFilter: '.*ad.*'),
              action: ContentBlockerAction(type: ContentBlockerActionType.BLOCK),
            ),
            ContentBlocker(
               trigger: ContentBlockerTrigger(urlFilter: '.*vidsrc.*'),
               action: ContentBlockerAction(type: ContentBlockerActionType.CSS_DISPLAY_NONE, selector: '.ad-banner'),
            ),
          ],
        ),
      ),
    );
  }
}
