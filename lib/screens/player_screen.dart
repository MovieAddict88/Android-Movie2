import 'package:flutter/material.dart';
import 'package:better_player/better_player.dart';
import '../models/movie.dart';

class PlayerScreen extends StatefulWidget {
  final StreamSource source;
  const PlayerScreen({super.key, required this.source});

  @override
  State<PlayerScreen> createState() => _PlayerScreenState();
}

class _PlayerScreenState extends State<PlayerScreen> {
  late BetterPlayerController _controller;

  @override
  void initState() {
    super.initState();
    BetterPlayerConfiguration betterPlayerConfiguration = const BetterPlayerConfiguration(
      aspectRatio: 16 / 9,
      fit: BoxFit.contain,
      autoPlay: true,
      looping: false,
      fullScreenByDefault: true,
      allowedScreenSleep: false,
    );

    BetterPlayerDataSource dataSource = BetterPlayerDataSource(
      BetterPlayerDataSourceType.network,
      widget.source.url,
      drmConfiguration: widget.source.isDrm
          ? BetterPlayerDrmConfiguration(
              drmType: BetterPlayerDrmType.widevine,
              licenseUrl: widget.source.drmLicenseUrl,
            )
          : null,
    );

    _controller = BetterPlayerController(betterPlayerConfiguration);
    _controller.setupDataSource(dataSource);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(title: const Text('Streaming')),
      body: Center(
        child: BetterPlayer(controller: _controller),
      ),
    );
  }
}
