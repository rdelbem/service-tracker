import { describe, it, expect } from 'vitest';
import { act, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { showAlert, showConfirm } from '../Modal';

describe('Modal helpers', () => {
  it('showConfirm resolves true when confirmed', async () => {
    const user = userEvent.setup();
    let promise: Promise<boolean>;
    await act(async () => {
      promise = showConfirm({ title: 'Confirm', message: 'Proceed?', confirmText: 'Yes' });
    });

    expect(await screen.findByText('Proceed?')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Yes' }));

    await expect(promise!).resolves.toBe(true);
    expect(screen.queryByText('Proceed?')).not.toBeInTheDocument();
  });

  it('showConfirm resolves false when cancelled', async () => {
    const user = userEvent.setup();
    let promise: Promise<boolean>;
    await act(async () => {
      promise = showConfirm({ message: 'Cancel me', cancelText: 'Nope' });
    });

    expect(await screen.findByText('Cancel me')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Nope' }));

    await expect(promise!).resolves.toBe(false);
  });

  it('showAlert resolves when dismissed', async () => {
    const user = userEvent.setup();
    let promise: Promise<void>;
    await act(async () => {
      promise = showAlert({ title: 'Notice', message: 'Done', confirmText: 'OK' });
    });

    expect(await screen.findByText('Done')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'OK' }));

    await expect(promise!).resolves.toBeUndefined();
    expect(screen.queryByText('Done')).not.toBeInTheDocument();
  });
});
