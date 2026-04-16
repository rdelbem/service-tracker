import { describe, it, expect, beforeEach, vi } from 'vitest';

const mockFetchGet = vi.hoisted(() => vi.fn());

vi.mock('../../utils/fetch', () => ({
  get: mockFetchGet,
}));

describe('analyticsStore', () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    (globalThis as any).data = {
      ...(globalThis as any).data,
      root_url: 'http://localhost',
      api_url: 'service-tracker-stolmc/v1',
      nonce: 'nonce',
    };
  });

  it('setPeriod updates selected period', async () => {
    const { useAnalyticsStore } = await import('../analyticsStore');

    useAnalyticsStore.getState().setPeriod('90');

    expect(useAnalyticsStore.getState().period).toBe('90');
  });

  it('fetchAnalytics populates analytics and clears loading', async () => {
    const payload = { summary: { total_customers: 1 } };
    mockFetchGet.mockResolvedValue({ data: payload });

    const { useAnalyticsStore } = await import('../analyticsStore');

    await useAnalyticsStore.getState().fetchAnalytics('30');

    expect(mockFetchGet).toHaveBeenCalledTimes(1);
    expect(mockFetchGet.mock.calls[0][0]).toContain('/analytics?');
    expect(mockFetchGet.mock.calls[0][0]).toContain('start=');
    expect(mockFetchGet.mock.calls[0][0]).toContain('end=');
    expect(useAnalyticsStore.getState().analytics).toEqual(payload);
    expect(useAnalyticsStore.getState().loading).toBe(false);
  });

  it('fetchAnalytics handles errors and clears loading', async () => {
    mockFetchGet.mockRejectedValue(new Error('boom'));
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    const { useAnalyticsStore } = await import('../analyticsStore');

    await useAnalyticsStore.getState().fetchAnalytics('7');

    expect(consoleSpy).toHaveBeenCalled();
    expect(useAnalyticsStore.getState().loading).toBe(false);
    expect(useAnalyticsStore.getState().analytics).toBeNull();
  });
});
