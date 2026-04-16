import { describe, it, expect, beforeEach, vi } from 'vitest';

const { mockGet, mockPost, mockPut } = vi.hoisted(() => ({
  mockGet: vi.fn(),
  mockPost: vi.fn(),
  mockPut: vi.fn(),
}));

vi.mock('../../utils/fetch', () => ({
  get: mockGet,
  post: mockPost,
  put: mockPut,
}));

describe('clientsStore', () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    (globalThis as any).data = {
      ...(globalThis as any).data,
      users_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/users',
      create_user_api_url: 'http://localhost/wp-json/service-tracker-stolmc/v1/users/create',
      root_url: 'http://localhost',
      api_url: 'service-tracker-stolmc/v1',
      nonce: 'nonce',
    };
    mockGet.mockResolvedValue({
      data: { data: [], page: 1, per_page: 6, total: 0, total_pages: 1 },
    });
  });

  it('getUsers populates user list and paging', async () => {
    mockGet.mockResolvedValue({
      data: { data: [{ id: '1', name: 'John' }], page: 2, per_page: 6, total: 7, total_pages: 2 },
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
      data: { data: [{ id: '2', name: 'Jane' }], page: 2, per_page: 6, total: 10, total_pages: 2 },
    });

    const { useClientsStore } = await import('../clientsStore');
    useClientsStore.setState({ totalPages: 2, searchQuery: 'ja', perPage: 6, page: 1 });

    await useClientsStore.getState().setPage(99);

    expect(useClientsStore.getState().page).toBe(2);
    expect(useClientsStore.getState().users[0].name).toBe('Jane');
    expect(String(mockGet.mock.calls.at(-1)?.[0])).toContain('page=2');
  });

  it('createUser refreshes list on success', async () => {
    mockPost.mockResolvedValue({ data: { success: true, message: 'created' } });

    const { useClientsStore } = await import('../clientsStore');
    const getUsersSpy = vi.spyOn(useClientsStore.getState(), 'getUsers').mockResolvedValue();

    const res = await useClientsStore.getState().createUser({ name: 'A', email: 'a@a.com' });

    expect(res.success).toBe(true);
    expect(getUsersSpy).toHaveBeenCalledWith(1);
  });

  it('updateUser updates only matching user and always clears loading', async () => {
    mockPut.mockResolvedValue({ data: { success: true } });
    const { useClientsStore } = await import('../clientsStore');

    useClientsStore.setState({
      users: [
        { id: '1', name: 'John', email: 'j@x.com' } as any,
        { id: '2', name: 'Jane', email: 'ja@x.com' } as any,
      ],
      loading: false,
    });

    await useClientsStore.getState().updateUser('2', { name: 'Jane Updated' });

    const users = useClientsStore.getState().users;
    expect(users[0].name).toBe('John');
    expect(users[1].name).toBe('Jane Updated');
    expect(useClientsStore.getState().loading).toBe(false);
  });
});
