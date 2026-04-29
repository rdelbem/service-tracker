import { describe, it, expect, beforeEach, vi } from 'vitest';

const { mockGet } = vi.hoisted(() => ({
  mockGet: vi.fn(),
}));

vi.mock('../../utils/fetch', () => ({
  get: mockGet,
}));

describe('clientsStore', () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    (globalThis as any).stolmcData = {
      ...(globalThis as any).stolmcData,
      users_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/users',
      root_url: 'http://localhost',
      api_url: 'service-tracker-stolmc/v1',
      nonce: 'nonce',
    };
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [],
        error_code: null,
        message: null,
        meta: { pagination: { page: 1, per_page: 6, total: 0, total_pages: 1 } },
      },
    });
  });

  it('getUsers populates user list and paging', async () => {
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [{ id: '1', name: 'John' }],
        error_code: null,
        message: null,
        meta: { pagination: { page: 2, per_page: 6, total: 7, total_pages: 2 } },
      },
    });

    const { useClientsStore } = await import('../clientsStore');

    await useClientsStore.getState().getUsers(2);

    expect(useClientsStore.getState()).toMatchObject({
      users: [{ id: '1', name: 'John' }],
      page: 2,
      total: 7,
      totalPages: 2,
      loadingUsers: false,
    });
  });

  it('searchUsers empty query falls back to getUsers', async () => {
    const { useClientsStore } = await import('../clientsStore');
    const spy = vi.spyOn(useClientsStore.getState(), 'getUsers');

    await useClientsStore.getState().searchUsers('   ');

    expect(spy).toHaveBeenCalledWith(1);
  });

  it('setPage clamps and fetches search page when searchQuery exists', async () => {
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [{ id: '2', name: 'Jane' }],
        error_code: null,
        message: null,
        meta: { pagination: { page: 2, per_page: 6, total: 10, total_pages: 2 } },
      },
    });

    const { useClientsStore } = await import('../clientsStore');
    useClientsStore.setState({ totalPages: 2, searchQuery: 'ja', perPage: 6, page: 1 });

    await useClientsStore.getState().setPage(99);

    expect(useClientsStore.getState().page).toBe(2);
    expect(useClientsStore.getState().users[0].name).toBe('Jane');
    expect(String(mockGet.mock.calls.at(-1)?.[0])).toContain('page=2');
  });
});
