import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/data_provider.dart';
import '../models/live_tv.dart';
import '../models/movie.dart';
import '../screens/player_screen.dart';

class LiveTvScreen extends StatelessWidget {
  const LiveTvScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Live TV')),
      body: Consumer<DataProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
             return const Center(child: CircularProgressIndicator(color: Colors.redAccent));
          }
          if (provider.liveChannels.isEmpty) {
             return const Center(child: Text('No channels available.'));
          }

          return GridView.builder(
            padding: const EdgeInsets.all(16),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.5,
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
            ),
            itemCount: provider.liveChannels.length,
            itemBuilder: (context, index) {
              final channel = provider.liveChannels[index];
              return ChannelCard(channel: channel);
            },
          );
        },
      ),
    );
  }
}

class ChannelCard extends StatelessWidget {
  final LiveChannel channel;
  const ChannelCard({super.key, required this.channel});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (_) => PlayerScreen(source: StreamSource(
          url: channel.streamUrl,
          quality: 'Auto',
          isDrm: channel.isDrm,
          drmLicenseUrl: channel.drmLicenseUrl,
        ))));
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.grey[900],
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.live_tv, size: 40, color: Colors.redAccent),
            const SizedBox(height: 8),
            Text(channel.title, style: const TextStyle(fontWeight: FontWeight.bold)),
            if (channel.category != null)
              Text(channel.category!, style: TextStyle(color: Colors.grey, fontSize: 12)),
          ],
        ),
      ),
    );
  }
}
