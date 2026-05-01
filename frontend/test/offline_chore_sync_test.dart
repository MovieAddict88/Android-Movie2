import 'package:flutter_test/flutter_test.dart';
import 'package:chorequest/services/offline_chore_sync.dart'; // Assuming package name
import 'package:hive/hive.dart';
import 'package:hive_test/hive_test.dart';

void main() {
  setUp(() async {
    await setUpTestHive();
  });

  tearDown(() async {
    await tearDownTestHive();
  });

  test('offline chore sync adds to pending queue', () async {
    // This test is conceptual as we don't have the full environment
    // but demonstrates the testing strategy
    final syncService = OfflineChoreSync();
    await syncService.init();

    // Mocking chore box data
    final box = await Hive.openBox('chores');
    await box.put(1, {'id': 1, 'name': 'Test', 'status': 'pending'});

    await syncService.completeChore(1);

    final updatedChore = box.get(1);
    expect(updatedChore['status'], 'completed');

    final pendingBox = Hive.box('pending_sync');
    expect(pendingBox.length, 1);
    expect(pendingBox.getAt(0)['chore_id'], 1);
  });
}
