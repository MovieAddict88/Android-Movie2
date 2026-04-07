import 'package:flutter/material.dart';
import '../models/movie.dart';
import '../models/series.dart';
import '../screens/player_screen.dart';
import '../screens/embed_player_screen.dart';

class DetailsScreen extends StatelessWidget {
  final dynamic item; // Can be Movie or Series
  const DetailsScreen({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    bool isMovie = item is Movie;

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 250,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Image.network(
                item.backdropUrl ?? item.posterUrl ?? '',
                fit: BoxFit.cover,
                errorBuilder: (context, _, __) => Container(color: Colors.grey[900]),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.title,
                    style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.star, color: Colors.amber, size: 20),
                      const SizedBox(width: 4),
                      Text(item.rating?.toString() ?? 'N/A'),
                      const SizedBox(width: 16),
                      if (isMovie) Text(item.releaseDate ?? ''),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(item.description ?? 'No description available.'),
                  const SizedBox(height: 24),
                  if (isMovie) ...[
                    const Text('Available Streams', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    ... (item as Movie).sources?.map((source) => ListTile(
                      leading: const Icon(Icons.play_arrow),
                      title: Text(source.quality),
                      subtitle: Text(source.platform ?? 'Default'),
                      onTap: () => _play(context, source),
                    )).toList() ?? [const Text('No streams available.')],
                  ] else ...[
                     const Text('Seasons', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                     const SizedBox(height: 8),
                     ... (item as Series).seasons.map((season) => ExpansionTile(
                       title: Text('Season ${season.number}'),
                       children: season.episodes.map((ep) => ListTile(
                         title: Text('Episode ${ep.episodeNumber}: ${ep.title}'),
                         onTap: () => _play(context, ep.sources.first),
                       )).toList(),
                     )).toList(),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _play(BuildContext context, StreamSource source) {
    if (source.url.contains('iframe') || source.url.contains('embed')) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => EmbedPlayerScreen(url: source.url)));
    } else {
      Navigator.push(context, MaterialPageRoute(builder: (_) => PlayerScreen(source: source)));
    }
  }
}
